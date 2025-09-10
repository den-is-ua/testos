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

# ProductBase
### Go under docker container
```
docker exec -it product-base-app bash
```

### Testing
```
docker exec -it product-base-app php artisan test
```