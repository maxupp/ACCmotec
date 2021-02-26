FROM python:3.8-slim


COPY . /app
RUN pip install -r /app/requirements.txt


# TODO: add cronjob

ENTRYPOINT ["python", "/app/load_data.py"]