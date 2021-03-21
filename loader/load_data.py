import argparse
import datetime
import os
import glob
from pathlib import Path
from zipfile import ZipFile
import time

import numpy as np
import pymysql.cursors

from ldparser import ldHead, laps, laps_times


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


def process_uploads(motec_path):
    motec_path = Path(motec_path)
    for zipf in glob.glob(str(motec_path / '*.zip')):
        # extract ld files
        with ZipFile(str(motec_path / zipf)) as motec_zip:
            to_extract = [n for n in motec_zip.namelist() if n.endswith(('ld', 'ldx'))]

            for p in to_extract:
                motec_zip.extract(p, path=str(motec_path))

        # remove processed zip
        os.remove(str(motec_path / zipf))


def read_motec_files(motec_path):
    motec_path = Path(motec_path)

    meta_data = []

    # read files and process
    for name in glob.glob(str(motec_path / '*.ld')):
        ldx_name = os.path.splitext(name)[0]+".ldx"
        if not os.path.isfile(ldx_name):
            print(f'Missing ldx file for : {name}')
            continue

        # extract meta info from file name
        try:
            track, car, weird_number, date, time = Path(name).stem.split('-')
        except:
            print(f'Unreadable filename: {name}')
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


if __name__ == "__main__":

    # wait a while until db is up
    time.sleep(30)
    print('Processing new uploads...')
    process_uploads(os.environ['DATA_PATH'])

    print('Reading motec data...')
    motec_data = read_motec_files(os.environ['DATA_PATH'])
    print(f'Read {len(motec_data)} motec files.')

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
            print(f'Fetched {len(existing_files)} records.')
            
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

    print(f'Inserted {cnt} new records.')
