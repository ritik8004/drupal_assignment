#!/usr/bin/env bash

set -e

# Get all changed files.
CHANGED_FILES="$1"

# Run tests.

# Composer check
if [[ ${CHANGED_FILES} =~ "composer.lock" || ${CHANGED_FILES} =~ "composer.json" ]]
then
  blt validate:composer
else
  echo "Skipping validate:composer checks as there is no change in Composer."
fi

# PHP check
if [[ ${CHANGED_FILES} =~ ".php" || ${CHANGED_FILES} =~ ".theme" || ${CHANGED_FILES} =~ ".module" || ${CHANGED_FILES} =~ ".inc" || ${CHANGED_FILES} =~ ".install"]]
then
  blt validate:php
  blt validate:phpcs
else
  echo "Skipping validate:php checks as there is no change in PHP files."
fi

# Yaml check
if [[ ${CHANGED_FILES} =~ ".yml" || ${CHANGED_FILES} =~ ".yaml" ]]
then
  blt validate:yaml
else
  echo "Skipping validate:yaml checks as there is no change in YAML files."
fi

# Twig check
if [[ ${CHANGED_FILES} =~ ".twig" ]]
then
  blt validate:twig
else
  echo "Skipping validate:twig checks as there is no change in Twig files."
fi

blt validate:acsf

# Drupal installtion check
if [[ ${CHANGED_FILES} =~ ".info.yml" || ${CHANGED_FILES} =~ ".services.yml" || ${CHANGED_FILES} =~ "config/install" || ${CHANGED_FILES} =~ "config/optional" || ${CHANGED_FILES} =~ ".install" ]]
then
  # Setup site.
  blt setup:settings --define environment=ci --no-interaction --verbose
  blt setup:hash-salt --define environment=ci --no-interaction --verbose
  blt setup:drupal:install --define environment=ci --no-interaction --verbose

  cd "$GITHUB_WORKSPACE/docroot"

  # Setup brand.
  drush @self --uri=default alshaya-post-drupal-install --brand_module="alshaya_vs" --country_code="ae"

  drush status
  drush pml

else
  echo "Drupal installation is skipped as not required..."
fi

exit;
