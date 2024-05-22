#!/bin/bash
set -e

echo start docker container

#docker compose build --no-cache
docker compose stop
docker compose rm -f
docker compose up -d
docker compose exec -u root php /tools/setup.sh

composer install
composer test
