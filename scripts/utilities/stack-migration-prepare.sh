#!/bin/bash
#
# This script does rsync for files of a site between stacks.
#
# ./scripts/utilities/stack-migration-prepare.sh 01live vskw 02live vskw2

source_env="$1"
source_site="$2"
target_env="$3"
target_site="$4"

if [[ -z "$source_env" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh 01live vskw 02live vskw2"
  exit
fi

if [[ -z "$source_site" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh 01live vskw 02live vskw2"
  exit
fi

if [[ -z "$target_env" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh 01live vskw 02live vskw2"
  exit
fi

if [[ -z "$target_site" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh 01live vskw 02live vskw2"
  exit
fi

if [ ${target_env:0:2} == "02" ]; then
  target_var="alshaya202live"
  target="alshaya2.02live@web-4238.enterprise-g1.hosting.acquia.com"
elif [ ${target_env:0:2} == "01" ]; then
  target_var="alshaya01live"
  target="alshaya.01live@web-1503.enterprise-g1.hosting.acquia.com"
else
  echo "Invalid target env $target_env"
  exit
fi

if [ ${source_env:0:2} == "02" ]; then
  source_var="alshaya202live"
  source="alshaya2.02live@web-4238.enterprise-g1.hosting.acquia.com"
elif [ ${source_env:0:2} == "01" ]; then
  source_var="alshaya01live"
  source="alshaya.01live@web-1503.enterprise-g1.hosting.acquia.com"
else
  echo "Invalid source env $source_env"
  exit
fi

cd /var/www/html/${source_var}/docroot

echo
echo "Syncing files with target env for $source_site"
source_files_folder=`drush -l $source_site.factory.alshaya.com status | grep Public | cut -d":" -f2 | sed 's/ //g'`
target_files_folder=`ssh -t $target "cd /var/www/html/$target_var/docroot; drush -l $target_site.factory.alshaya.com status | grep Public | cut -d":" -f2 | sed 's/ //g'"`
rsync -a $source_files_folder $target:$target_files_folder

echo
