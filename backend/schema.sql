CREATE DATABASE motec_db;
USE motec_db;

GRANT SELECT, INSERT ON motec_db.* to motec;

CREATE TABLE telemetry
(
    id int NOT NULL AUTO_INCREMENT,
    filename TEXT,
    track TEXT,
    car TEXT,
    date TEXT,
    time TEXT,
    best_time TEXT,
    best_lap TEXT, 
    PRIMARY KEY(id)
)