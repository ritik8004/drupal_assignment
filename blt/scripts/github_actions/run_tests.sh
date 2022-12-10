#!/usr/bin/env bash

set -e

# Get all changed files.
CHANGED_FILES="$(cat $1)"
echo "Changed files: ${CHANGED_FILES}"

# Run tests.

# Composer check
blt validate:composer

# PHP check
if [[ ${CHANGED_FILES} =~ ".profile" || ${CHANGED_FILES} =~ ".php" || ${CHANGED_FILES} =~ ".theme" || ${CHANGED_FILES} =~ ".module" || ${CHANGED_FILES} =~ ".inc" || ${CHANGED_FILES} =~ ".install" ]]
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

# Drupal installation check if change in docroot directory in specific files.
if [[ ${CHANGED_FILES} =~ "docroot/" && ( ${CHANGED_FILES} =~ ".profile" || ${CHANGED_FILES} =~ ".module" || ${CHANGED_FILES} =~ ".theme" || ${CHANGED_FILES} =~ ".yml" || ${CHANGED_FILES} =~ ".install" ) ]]
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
