#!/usr/bin/env bash

set -e

# Setup site.
blt validate

blt setup:settings --define environment=ci --no-interaction --verbose
blt setup:hash-salt --define environment=ci --no-interaction --verbose
blt setup:drupal:install --define environment=ci --no-interaction --verbose

cd "$GITHUB_WORKSPACE/docroot"

# Setup brand.
drush @self --uri=default alshaya-post-drupal-install --brand_module="alshaya_vs" --country_code="ae"

drush status
drush pml
