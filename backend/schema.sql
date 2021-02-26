CREATE DATABASE motec_db;
USE motec_db;

CREATE TABLE telemetry
(
    id int NOT NULL AUTO_INCREMENT,
    filename TEXT,
    track TEXT,
    car TEXT,
    date TEXT,
    time TEXT,
    besttime TEXT,
    bestlap TEXT, 
    PRIMARY KEY(id)
)