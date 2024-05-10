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

# the recieps are not yet working
curl "https://raw.githubusercontent.com/sitepark/symfony-recipes/main/atoolo/graphql-search-bundle/1.0/config/packages/graphql.yaml" > config/packages/graphql.yaml

# we do not have an atoolo-search-bundle yet
sed -i 's#configure your dependencies.#configure your dependencies.\n\nimports:\n  - { resource\: ../vendor/atoolo/search/config/commands.yml }#' config/services.yaml

composer require --no-interaction atoolo/graphql-search-bundle:dev-feature/core-name

# currenty no recieps for security bundle
composer require atoolo/security-bundle:dev-main || true
rm config/packages/security.yaml
cat >config/routes/atoolo-security.yaml <<EOL
atoolo_security_bundle:
    resource: '@AtooloSecurityBundle/Resources/config/routing/login_check.yaml'
EOL

bin/console cache:clear

composer require symfony/monolog-bundle

./bin/console lexik:jwt:generate-keypair
