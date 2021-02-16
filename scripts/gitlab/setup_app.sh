#!/usr/bin/env bash

set -ev

mysql -u drupal -h mysql -pdrupal -P 3306 -e "show databases"
vendor/bin/blt setup --define drush.alias='${drush.aliases.ci}' --environment=ci --no-interaction --ansi --verbose

set +v
