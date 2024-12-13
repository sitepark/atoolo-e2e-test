#!/bin/bash
set -e

touch data/php/.bash_history

echo start docker container



# to run outside of github actions
if [ "$MATRIX_PHP_VERSION" == "" ]; then
    export MATRIX_PHP_VERSION=8.2
    export SOLR_MAPPING_PORT=9091
    export APACHE_MAPPING_PORT=9090
    export FPM_MAPPING_PORT=9191
    export MAILPIT_SMTP_MAPPING_PORT=1025
    export MAILPIT_HTTP_MAPPING_PORT=8025
else
    export VERSION_AS_NUMBER=${MATRIX_PHP_VERSION/./""}
    export SOLR_MAPPING_PORT=${VERSION_AS_NUMBER}91
    export APACHE_MAPPING_PORT=${VERSION_AS_NUMBER}90
    export FPM_MAPPING_PORT=${VERSION_AS_NUMBER}92
    export MAILPIT_SMTP_MAPPING_PORT=${VERSION_AS_NUMBER}25
    export MAILPIT_HTTP_MAPPING_PORT=${VERSION_AS_NUMBER}80
    export DOCKER_COMPOSE_PROJECT_NAME=atoolo-e2e-test-php${VERSION_AS_NUMBER}
fi

export ENDPOINT_BASE=http://atoolo-e2e-test:${APACHE_MAPPING_PORT}
export MAILPIT_ENDPOINT_BASE=http://atoolo-e2e-test:${MAILPIT_HTTP_MAPPING_PORT}

#docker compose --project-name ${DOCKER_COMPOSE_PROJECT_NAME} build
#docker compose --project-name ${DOCKER_COMPOSE_PROJECT_NAME} build --no-cache
docker compose --project-name "${DOCKER_COMPOSE_PROJECT_NAME}" stop
docker compose --project-name "${DOCKER_COMPOSE_PROJECT_NAME}" rm -f
docker compose --project-name "${DOCKER_COMPOSE_PROJECT_NAME}" up -d
docker compose --project-name "${DOCKER_COMPOSE_PROJECT_NAME}" exec -u root php /tools/setup.sh

# create composer.lock
composer update --no-install
# install dependencies with exists composer.lock so that post-install-cmd scripts are also executed
composer install
composer test

# Without DOCKER_COMPOSE_PROJECT_NAME, it is assumed that the test is executed locally.
# In this case, we leave the containers started so that the tests can simply be repeated
# with `composer test` or via IDE.
if [ "${DOCKER_COMPOSE_PROJECT_NAME}" != "" ]; then
  echo stop ${DOCKER_COMPOSE_PROJECT_NAME}
  docker compose --project-name "${DOCKER_COMPOSE_PROJECT_NAME}" stop
  docker compose --project-name "${DOCKER_COMPOSE_PROJECT_NAME}" rm -f
fi
