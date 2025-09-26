#!/bin/bash
docker compose up -d

docker exec -d admin-app npm run dev

echo "Waiting for running RabbitMQ"
sleep 30

docker exec -d admin-app php artisan amq:recieve-import-progress

docker exec -d import-app php artisan queue:work --queue=default
docker exec -d import-app php artisan amq:recieve-import-confirmations

docker exec -d product-base-app php artisan amq:recieve-products

echo "All services are started"