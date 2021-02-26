import argparse
import datetime
import os
import glob
from pathlib import Path
import time

import xml.etree.ElementTree as ET
import pandas as pd
import numpy as np
from sqlalchemy import create_engine
import pymysql

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

    meta_data = {}

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
    
        meta_data[name] = {
            'track': track,
            'car': car,
            'date': date,
            'time': time,
            'best_time': best_time,
            'best_lap': best_lap
        }

    return pd.DataFrame.from_dict(meta_data, orient='index')


if __name__ == "__main__":

    # wait a while until db is up
    time.sleep(10)

    motec_data = read_motec_files(os.environ['DATA_PATH'])

    connect_string = 'mysql+pymysql://{}:{}@{}/{}'.format(
        os.environ['MYSQL_USER'],
        os.environ['MYSQL_PASSWORD'],
        os.environ['MYSQL_HOST'],
        os.environ['MYSQL_DATABASE'],
    )

    engine = create_engine(connect_string, pool_recycle=3000)
    connection = engine.connect()

    try:
        frame = motec_data.to_sql(
            'telemetry', 
            connection,
            flavor='mysql', 
            if_exists='append', 
            index=False)
    except Exception as e:
        print(e)
    else:
        print('Data loaded successfully.')
    finally:
        connection.close()

