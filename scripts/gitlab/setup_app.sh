#!/usr/bin/env bash

set -ev

drush sql-connect
vendor/bin/blt setup --define drush.alias='${drush.aliases.ci}' --environment=ci --no-interaction --ansi --verbose

set +v
