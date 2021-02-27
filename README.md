# ACC Motec File Server

This repository provides infrastructure to host an archive of MoTec data.
There are three Docker containers, a MySQL database backend, a PHP frontend, and a Python container which reads and analyzes the actual MoTec files.


## Installation
1. Clone the repository.
2. Install Docker + Docker-Compose if necessary.
3. `docker-compose build`
4. `docker-compose up`


## Configuration
All configuration, such as passwords and paths, can be done in the `docker-compose.yaml`.