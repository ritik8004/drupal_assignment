#!/usr/bin/env bash

set -ev

# We don't want to run Behat for now (no valuable outcome yet).
#vendor/bin/blt tests --define drush.alias='${drush.aliases.ci}' -D behat.web-driver=chrome --no-interaction --ansi --verbose

set +v
