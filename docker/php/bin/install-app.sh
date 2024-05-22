#!/bin/bash
set -e

PROJECT_DIR=$1
export HOME=/apps

cd $HOME

git config --global user.email "opensource@sitepark.com"
git config --global user.name "Atoolo e2e test bot"

symfony new $PROJECT_DIR --version="^7.0"

cd $HOME/$PROJECT_DIR

composer config extra.symfony.allow-contrib true
composer config --json extra.symfony.endpoint \
'["'\
'https://api.github.com/repos/sitepark/'\
'symfony-recipes/contents/index.json'\
'?ref=flex/main'\
'"]'
composer config --json --merge extra.symfony.endpoint \
'["flex://defaults"]'

composer config minimum-stability dev
composer config platform-check true

# install toolo suite
composer require --no-interaction \
    atoolo/deployment-bundle:dev-main  \
    atoolo/graphql-search-bundle:dev-main \
    atoolo/security-bundle:dev-main \
    symfony/monolog-bundle

./bin/console lexik:jwt:generate-keypair
