#!/usr/bin/env bash

docker restart admin-app
docker restart import-app
docker restart product-base-app

docker exec -d admin-app php artisan amq:recieve-import-progress
docker exec -d admin-app npm run dev
docker exec -d import-app php artisan queue:work --queue=default
docker exec -d import-app php artisan amq:recieve-import-confirmations
docker exec -d product-base-app php artisan amq:recieve-products
