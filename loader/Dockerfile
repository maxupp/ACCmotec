FROM python:3.8-slim

copy requirements.txt /app/requirements.txt
RUN pip install -r /app/requirements.txt
COPY . /app

ENTRYPOINT ["python", "/app/run.py"]