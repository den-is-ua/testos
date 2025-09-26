#!/bin/bash
docker compose up -d

docker exec -d admin-app npm run dev
docker exec -d admin-app php artisan amq:recieve-import-progress

docker exec -d import-app php artisan queue:work --queue=default
docker exec -d import-app php artisan amq:recieve-import-confirmations

docker exec -d product-base-app php artisan amq:recieve-products