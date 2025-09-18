# Install

```
bash install-with-docker.sh
```

Setup host
```
nano /etc/hosts

# Insert
# 127.0.0.1 product-base.loc
# 127.0.0.1 import.loc
127.0.0.1 admin.loc
```

Run queues
```
docker exec -it import-app php artisan queue:work rabbitmq --queue=import_confirmations,default
docker exec -it product-base-app php artisan queue:work rabbitmq --queue=imports
```

Run vite
```
docker exec admin-app npm run dev
```

# Postman api collection
Download the dump file and import to Postman
[File](.postman/TestOS.postman_collection.json)

# ProductBase
### Go under docker container
```
docker exec -it product-base-app bash
```

### Testing
```
docker exec -it product-base-app php artisan test
```

# Import
Setup `GEMINI_API_KEY` in the `.env` file
[Get an api key](https://aistudio.google.com/app/apikey)

### Go under docker container
```
docker exec -it import-app bash
```

### Testing
```
docker exec -it import-app php artisan test