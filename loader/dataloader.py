import tempfile
import os
import logging
import glob
import time
from pathlib import Path
from shutil import move
from zipfile import ZipFile

import numpy as np
import pymysql.cursors

from ldparser import ldHead, laps, laps_times

logging.basicConfig(filename='/var/log/motec_loader.log',level=logging.INFO,
        format='%(asctime)s : %(message)s',
        datefmt='%d.%m.%Y %I:%M:%S %p')


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
        with ZipFile(str(upload_dir / file)) as zf:
            zf.extractall(temp)

        # find ld and ldx files in temp dir
        lds = glob.glob(str(Path(temp) / '*.ld'))
        ldxs = glob.glob(str(Path(temp) / '*.ldx'))

        # check for mismatched or unmatched files
        ld_stems = set([Path(x).stem for x in lds])
        ldx_stems = set([Path(x).stem for x in ldxs])

        mismatched = ldx_stems.symmetric_difference(ld_stems)

        if mismatched:
            report += 'Missing either ld or ldx:\n'
            for m in mismatched:
                report += f'\t{m}'

        # check the remaining names for parsability
        common_names = set(ld_stems).intersection(set(ldx_stems))

        names_to_copy = []
        for name in common_names:
            parts = name.split('-')
            if len(parts) != 5:
                report += f'Invalid filename: {name}'
            else:
                names_to_copy.append(name)

        # copy valid pairs to motec dir
        motec_dir = Path(os.environ['MOTEC_PATH'])
        for name in names_to_copy:
            src = str(Path(temp) / name)
            dst = str(motec_dir / name)
            move(src + '.ld', dst + '.ld')
            move(src + '.ldx', dst + '.ldx')

        if report == '':
            update_index()
            report += f'Successfully imported {len(names_to_copy)} files.'
            success = True
        else:
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