import tempfile
import os
import logging
import glob
import time
from pathlib import Path
from shutil import copy, move
from zipfile import ZipFile

import numpy as np
import pymysql.cursors

from ldparser import ldHead, laps, laps_times

logging.basicConfig(filename='/var/log/motec_loader.log',level=logging.INFO,
        format='%(asctime)s : %(message)s',
        datefmt='%d.%m.%Y %I:%M:%S %p')


def flatten_dir(destination, depth=None):
    if not depth:
        depth = []
    joined_path = os.path.join(*([destination] + depth))
    logging.info(joined_path)
    for file_or_dir in os.listdir(joined_path):
        logging.info(file_or_dir)
        if os.path.isfile(os.path.join(joined_path, file_or_dir)):
            logging.info('File!')
            move(os.path.join(joined_path, file_or_dir), destination)
        else:
            logging.info('Dir!')
            flatten_dir(destination, depth + [file_or_dir])


def get_fastest_lap_from_ld(ld_path, track):
    head = ldHead.fromfile(open(ld_path, 'rb'))
    laps_ = laps_times(np.array(laps(ld_path)))

    # TODO: Filter out laptimes that are obviously too low
    min_times = {
        'barcelona': 100,
        'brands_hatch': 80,
        'donington': 82,
        'hungaroring': 100,
        'imola': 95,
        'kyalami': 96,
        'laguna_seca': 78,
        'misano': 90,
        'monza': 103,
        'mount_panorama': 115,
        'nurburgring': 110,
        'paul_ricard': 90,
        'snetterton': 100,
        'spa': 130,
        'silverstone': 113,
        'oulton_park': 90,
        'suzuka': 115,
        'zandvoort': 92,
        'zolder': 85
    }

    min_time = min_times[track.lower()]
    laps_ = [x for x in laps_ if x > min_time]

    if len(laps_) < 1:
        return None, None

    fastest_time = min(laps_)
    fastest_lap = laps_.index(fastest_time) + 1

    return fastest_time, fastest_lap


def process_uploaded_zip(body):
    logging.info(str(body))
    file = body['filename']
    upload_dir = Path(os.environ['UPLOADS_PATH'])

    report = ''
    logging.info(f'Processing {file}')

    # extract all files to temp dir
    with tempfile.TemporaryDirectory() as temp:
        try:
            with ZipFile(str(upload_dir / file)) as zf:
                zf.extractall(temp)
        except:
            return {'success': False, 'report': 'ZipFile could not be extracted, bad format?'}

        # flatten tempdir, so we don't have to deal with dir trees
        flatten_dir(temp)

        # find ld and ldx files in temp dir
        lds = Path(temp).rglob('*.ld')
        ldxs = Path(temp).rglob('*.ldx')

        # check for mismatched or unmatched files
        ld_stems = set([Path(x).stem for x in lds])
        ldx_stems = set([Path(x).stem for x in ldxs])

        mismatched = ldx_stems.symmetric_difference(ld_stems)

        if mismatched:
            report += 'Missing either ld or ldx:<br>'
            for m in mismatched:
                report += f'\t{m}<br>'

        # check the remaining names for parsability
        common_names = set(ld_stems).intersection(set(ldx_stems))        
        logging.info(f'Matching names: {len(common_names)}')


        names_to_copy = []
        for name in common_names:
            parts = name.split('-')
            if len(parts) != 5:
                report += f'Invalid filename: {name}<br>'
            else:
                names_to_copy.append(name)
		
        logging.info(f'Files to move: {len(names_to_copy)}')

        logging.info(os.listdir(temp))

        # copy valid pairs to motec dir
        motec_dir = Path(os.environ['MOTEC_PATH'])
        for name in names_to_copy:
            src = str(Path(temp) / name)
            dst = str(motec_dir / name)
            copy(src + '.ld', dst + '.ld')
            copy(src + '.ldx', dst + '.ldx')
		
        logging.info(f'Reached returning, report: {report}')

        if report == '':
            update_index()
            report += f'Successfully imported {len(names_to_copy)} files.'
            success = True
        else:
            report += 'No files were imported.'
            success = False

    return {'success': success, 'report': report}


def read_motec_files(motec_path):
    motec_path = Path(motec_path)

    meta_data = []

    # read files and process
    for name in glob.glob(str(motec_path / '*.ld')):
        ldx_name = os.path.splitext(name)[0]+".ldx"
        if not os.path.isfile(ldx_name):
            logging.warning(f'Missing ldx file for : {name}')
            continue

        # extract meta info from file name
        try:
            track, car, weird_number, date, time = Path(name).stem.split('-')
        except:
            logging.warning(f'Unreadable filename: {name}')
            continue

        # load ld file
        best_time, best_lap = get_fastest_lap_from_ld(motec_path / name, track)
        if best_time is None:
            # No valid laps in this motec log
            continue

        time = time.replace('.', ':')
    
        meta_data.append({
            'filename': Path(name).stem,
            'track': track,
            'car': car,
            'date': date,
            'time': time,
            'best_time': best_time,
            'best_lap': best_lap
        })

    return meta_data


def update_index():
    logging.info('Importing all existing motec data.')

    motec_data = read_motec_files(os.environ['MOTEC_PATH'])
    logging.info(f'Read {len(motec_data)} motec files.')

    connection = pymysql.connect(
        host=os.environ['MYSQL_HOST'],
        user=os.environ['MYSQL_USER'],
        password=os.environ['MYSQL_PASSWORD'],
        database=os.environ['MYSQL_DATABASE'],
        cursorclass=pymysql.cursors.DictCursor
    )

    with connection:
        with connection.cursor() as cursor:
            cursor.execute('SELECT filename FROM telemetry')
            existing_files = [x['filename'] for x in cursor.fetchall()]
            logging.info(f'Fetched {len(existing_files)} records.')
            
        with connection.cursor() as cursor:
            cnt = 0
            for data in motec_data:
                if data['filename'] in existing_files:
                    # discard telemetry that is in the DB already
                    continue

                # insert new telemtry
                cols = ['filename', 'track', 'car', 'date', 'time', 'best_time', 'best_lap']
                col_string = ', '.join(cols)
                value_string = ', '.join(['"' + str(data[x]) + '"' for x in cols])
                cursor.execute('INSERT INTO telemetry ({}) VALUES ({})'.format(col_string, value_string))
                cnt += 1

            connection.commit()

    logging.info(f'Inserted {cnt} new records.')


if __name__ == "__main__":
    # execute only if run as a script
    process_uploaded_zip('../motec_data/v3IBO8LypX.zip')
