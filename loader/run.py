import connexion
import dataloader
import time

app = connexion.FlaskApp(
    __name__, specification_dir='api_spec', options={"swagger_ui": False, "serve_spec": False}
)
app.add_api("api.yaml", strict_validation=True)

# wait a while for db server to be up and running, then update index once
time.sleep(20)
dataloader.update_index()

app.run(server='tornado', port=1337)
