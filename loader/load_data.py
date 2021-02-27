import argparse
import datetime
import os
import glob
from pathlib import Path
import time

import xml.etree.ElementTree as ET
import numpy as np
import pymysql.cursors

from ldparser import ldHead, laps, laps_times


def get_fastest_lap_from_ld(ld_path):
    head = ldHead.fromfile(open(ld_path, 'rb'))
    laps_ = laps_times(np.array(laps(ld_path)))

    if len(laps_) < 1:
        return None, None

    fastest_time = min(laps_)
    fastest_lap = laps_.index(fastest_time) + 1

    return fastest_time, fastest_lap


def read_motec_files(motec_path):
    motec_path = Path(motec_path)

    meta_data = []

    # read files and process
    for name in glob.glob(str(motec_path / '*.ldx')):

        # load ld file
        best_time, best_lap = get_fastest_lap_from_ld(str(motec_path / Path(name).stem) + '.ld')
        if best_time is None:
            # No valid laps in this motec log
            continue

        # extract meta info from file name
        track, car, weird_number, date, time = Path(name).stem.split('-')
        time = time.replace('.', ':')

        tree = ET.parse(name)
        detail_block = tree.find('.//Details')
        if detail_block:
            # extract info from detail block
            lap_time = tree.find(".//String[@Id ='Fastest Time']").get('Value')
        else:
            # infer lap times from markers
            beacon_times = [0.] + [float(x.get('Time')) for x in tree.findall(".//Marker")]
            if len(beacon_times) < 2:
                continue
            # calculate lap time
            lap_time = np.diff(beacon_times).min()
            lap_time = datetime.datetime.fromtimestamp(lap_time/1000000.0)
    
        meta_data.append({
            'filename': name,
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
                print('INSERT INTO telemetry ({}) VALUES ({})'.format(col_string, value_string))
                cursor.execute('INSERT INTO telemetry ({}) VALUES ({})'.format(col_string, value_string))
                cnt += 1
                
            connection.commit()

    print(f'Inserted {cnt} new records.')