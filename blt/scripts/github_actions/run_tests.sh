#!/usr/bin/env bash

set -e

# Get all changed files.
CHANGED_FILES="$1"

# Test drupal installation only when a new module is added or
# change in config files or
# install hook is added (change in install file) or
# change in services.yml as these could impact the installation process
INSTALL_DRUPAL="NO";
if [[ ${CHANGED_FILES} =~ ".info.yml" || ${CHANGED_FILES} =~ ".services.yml" || ${CHANGED_FILES} =~ "config/install" || ${CHANGED_FILES} =~ "config/optional" || ${CHANGED_FILES} =~ ".install" ]]
then
  INSTALL_DRUPAL="YES"
fi

# Run tests.
blt validate:composer
blt validate:php
blt validate:phpcs
blt validate:yaml
blt validate:twig
blt validate:acsf

if [ ${INSTALL_DRUPAL} == "NO" ]
then
   echo "Drupal installation is skipped as not required..."
   exit;
fi

echo "Installing Drupal..."

# Setup site.
blt setup:settings --define environment=ci --no-interaction --verbose
blt setup:hash-salt --define environment=ci --no-interaction --verbose
blt setup:drupal:install --define environment=ci --no-interaction --verbose

cd "$GITHUB_WORKSPACE/docroot"

# Setup brand.
drush @self --uri=default alshaya-post-drupal-install --brand_module="alshaya_vs" --country_code="ae"

drush status
drush pml
