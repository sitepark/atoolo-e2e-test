#!/bin/bash
set -e

touch data/php/.bash_history

echo start docker container

# to run outside of github actions
if [ "$MATRIX_PHP_VERSION" == "" ]; then
    export MATRIX_PHP_VERSION=8.2
fi

#docker compose build
#docker compose build --no-cache
docker compose stop
docker compose rm -f
docker compose up -d
docker compose exec -u root php /tools/setup.sh

composer install
composer test
