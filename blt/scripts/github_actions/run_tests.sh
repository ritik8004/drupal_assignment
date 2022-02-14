#!/usr/bin/env bash

set -e

# Run tests.
blt validate:composer
blt validate:php
blt validate:phpcs
blt validate:yaml
blt validate:twig

# Setup site.
blt setup:settings --define environment=ci --no-interaction --verbose
blt setup:hash-salt --define environment=ci --no-interaction --verbose
blt setup:drupal:install --define environment=ci --no-interaction --verbose

cd "$GITHUB_WORKSPACE/docroot"

# Setup brand.
drush @self --uri=default alshaya-post-drupal-install --brand_module="alshaya_vs" --country_code="ae"

drush status
drush pml
