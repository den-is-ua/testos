# Install

```
bash install-with-docker.sh
```

Setup host
```
nano /etc/hosts

# Insert
# 127.0.0.1 product-base.loc
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