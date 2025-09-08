#!/bin/bash
docker network create testos
docker compose build --build-arg uid=$(id -u) --build-arg gid=$(id -g)
docker compose up -d

docker exec -it testos-db psql -U postgres -d postgres -c "CREATE DATABASE product_base;"

docker exec product-base-app composer install
docker exec product-base-app cp .env.example .env
docker exec product-base-app php artisan key:generate
docker exec product-base-app php artisan migrate
