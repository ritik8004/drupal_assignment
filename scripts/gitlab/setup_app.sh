#!/usr/bin/env bash

set -ev

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" --host=mysql -e "show databases"
mysql -u drupal8 -h mysql -pdrupal8 -P 3306 -e "show databases"
mysql -u drupal -h mysql -pdrupal -P 3306 -e "show databases"
vendor/bin/blt setup --define drush.alias='${drush.aliases.ci}' --environment=ci --no-interaction --ansi --verbose

set +v
