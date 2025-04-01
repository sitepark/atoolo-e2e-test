#!/bin/bash
set -e

PROJECT_DIR=$1
export HOME=/apps

cd $HOME

git config --global user.email "opensource@sitepark.com"
git config --global user.name "Atoolo e2e test bot"

if [ "${GITHUB_TOKEN}" != "" ]; then
    composer config --global github-oauth.github.com ${GITHUB_TOKEN}
fi

symfony new $PROJECT_DIR --version="^7.2"

cd $HOME/$PROJECT_DIR

# symfony-recipes config
composer config extra.symfony.allow-contrib true
composer config --json extra.symfony.endpoint \
'["'\
'https://api.github.com/repos/sitepark/'\
'symfony-recipes/contents/index.json'\
'?ref=flex/main'\
'"]'
composer config --json --merge extra.symfony.endpoint \
'["flex://defaults"]'

# Atoolo runtime config
composer config --json extra.atoolo.runtime \
'{'\
'    "ini": {'\
'        "set": {'\
'            "date.timezone": "Europe/Berlin"'\
'        }'\
'    },'\
'    "umask": "0002",'\
'    "users": ['\
'        "www-data",'\
'        "{SCRIPT_OWNER}"'\
'    ]'\
'}'


composer config minimum-stability dev
composer config platform-check true

# install toolo suite
composer config --no-plugins allow-plugins.atoolo/runtime true
composer require --no-interaction \
    atoolo/runtime:dev-main \
    atoolo/runtime-check-bundle:dev-main \
    atoolo/deployment-bundle:dev-main \
    atoolo/resource-bundle:dev-main \
    atoolo/search-bundle:dev-main \
    atoolo/citygov-bundle:dev-main \
    atoolo/citycall-bundle:dev-main \
    atoolo/events-calendar-bundle:dev-main \
    atoolo/graphql-search-bundle:dev-main \
    atoolo/security-bundle:dev-main \
    atoolo/form-bundle:dev-feature/initial-implementation \
    atoolo/seo-bundle:dev-main \
    atoolo/rewrite-bundle:dev-main \
    atoolo/microsite-bundle:dev-main \
    symfony/monolog-bundle


./bin/console lexik:jwt:generate-keypair
