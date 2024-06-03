#!/bin/bash
set -e

/tools/create-cores.sh
su www-data -s /tools/install-app.sh atoolo-e2e-test
ln -s /apps/atoolo-e2e-test /var/www/atoolo-e2e-test/www/app
su www-data -s /var/www/atoolo-e2e-test/www/app/bin/console search:indexer
