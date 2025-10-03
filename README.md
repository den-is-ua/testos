# About project
The main goal of this project is expirience with major technology of web application like: `microservices`, `rabbitmq`, `pusher`, `postgres`, `AI assistent`, `vue`, `shadcn`
Main business logic of this project is: 
User can import `csv` file to fill/update products base. AI assistent parse file and setup propriate configs to import data by right structure from file.
User has small dashboard which display table of products and form to upload import file.



# Install
### Deploy all services
```
bash install-with-docker.sh
```

### Setup AI agent
Setup `GEMINI_API_KEY` in the `Import/.env` file
[Get an api key](https://aistudio.google.com/app/apikey). Its free ;)

### Setup pusher
Setup varialbes in the `Admin/.env`.
[Sign up and get access](https://pusher.com/) Its free also :)
```bash
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
PUSHER_PORT=
PUSHER_SCHEME=
```

### Optional setup hosts
```
nano /etc/hosts

# Insert
# 127.0.0.1 product-base.loc
# 127.0.0.1 import.loc
127.0.0.1 admin.loc
```

### Open project and use
Go to browser by url `admin.loc` or `127.0.0.1` if you didnt setup hosts

# Postman api collection
Download the dump file and import to Postman
[File](.postman/TestOS.postman_collection.json)
