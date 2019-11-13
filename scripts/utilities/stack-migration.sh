#!/bin/bash
#
# This script migrates given site between stacks.
#
# ./scripts/utilities/stack-migration.sh 01live vskw 02live vskw2

source_env="$1"
source_site="$2"
target_env="$3"
target_site="$4"

type="$3"
if [[ -z "$source_env" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration.sh 01live vskw 02live vskw2"
  exit
fi

if [[ -z "$source_site" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration.sh 01live vskw 02live vskw2"
  exit
fi

if [[ -z "$target_env" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration.sh 01live vskw 02live vskw2"
  exit
fi

if [[ -z "$target_site" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration.sh 01live vskw 02live vskw2"
  exit
fi

if [ ${target_env:0:2} != "02" ]; then
  target_var="alshaya202live"
  target="alshaya2.02live@web-4238.enterprise-g1.hosting.acquia.com"
else
  target_var="alshaya01live"
  target="alshaya.01live@web-1503.enterprise-g1.hosting.acquia.com"
fi

if [ ${source_env:0:2} != "02" ]; then
  source_var="alshaya202live"
  source="alshaya2.02live@web-4238.enterprise-g1.hosting.acquia.com"
else
  source_var="alshaya01live"
  source="alshaya.01live@web-1503.enterprise-g1.hosting.acquia.com"
fi

cd /var/www/html/${source_var}/docroot

echo
echo "Enabling maintenance mode"
echo
drush -l $source_site.factory.alshaya.com sset system.maintenance_mode TRUE

echo
echo "Syncing files with target env for $source_site"
source_files_folder=`drush -l $source_site.factory.alshaya.com status | grep Public | cut -d":" -f2 | sed 's/ //g'`
target_files_folder=`ssh -t $target "cd /var/www/html/$target_var/docroot; drush -l $target_site.factory.alshaya.com status | grep Public | cut -d":" -f2 | sed 's/ //g'"`
screen -S rsync_${source_site}_${target_site} -dm bash -c "rsync -a $source_files_folder $target:$target_files_folder"

echo
echo "Dumping database..."
mkdir -p /tmp/migrate

echo
echo "Dumping databases for $source_site"
drush -l $source_site.factory.alshaya.com sql-dump --result-file=/tmp/migrate/$source_site.sql --skip-tables-key=common --gzip

echo
echo "Copying the dump to $target_env env..."
ssh $target 'mkdir -p /tmp/migrate'
scp /tmp/migrate/* $remote_user@$remote_host:/tmp/migrate/

echo
echo "Importing the dump on $target_env env..."
ssh $target 'gunzip /tmp/migrate/*.gz'

echo
echo "Droppping and importing database again for $target_site"
ssh $target "cd /var/www/html/$target_var/docroot; drush -l $target_site.factory.alshaya.com sql-drop -y; drush -l $target_site.factory.alshaya.com sql-cli < /tmp/migrate/$source_site.sql"

echo
echo "Removing temp directories for sql dumps in source and target envs"
rm -rf /tmp/migrate
ssh $target 'rm -rf /tmp/migrate'

echo
