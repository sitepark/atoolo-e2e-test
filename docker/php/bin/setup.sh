#!/bin/bash
set -e

php --version

/tools/create-cores.sh
su www-data -s /bin/bash -c "/tools/install-app.sh atoolo-e2e-test"
ln -s /apps/atoolo-e2e-test /var/www/atoolo-e2e-test/www/app
su www-data -s /bin/bash -c "/var/www/atoolo-e2e-test/www/app/bin/console search:indexer"
# run runtime-check-scheduler once
su www-data -s /bin/bash -c "/var/www/atoolo-e2e-test/www/app/bin/console messenger:consume --limit 1 scheduler_atoolo-runtime-check"
