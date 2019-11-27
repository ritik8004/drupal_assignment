#!/bin/bash
#
# This script migrates given site between stacks.
#
# ./scripts/utilities/stack-migration.sh 01live vskw 02live vskw2

source_env="$1"
source_site="$2"
target_env="$3"
target_site="$4"

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

echo
source_alias=`drush sa | grep "$source_env\$"`
if [[ -z "$source_alias" ]]; then
  echo "Invalid source env $source_env"
  exit
fi

target_alias=`drush sa | grep "$target_env\$"`
if [[ -z "$target_alias" ]]; then
  echo "Invalid target env $target_env"
  exit
fi

source_root=`drush sa $source_alias | grep root | cut -d"'" -f4`

target_root=`drush sa $target_alias | grep root | cut -d"'" -f4`
target_remote_user=`drush sa $target_alias | grep remote-user | cut -d"'" -f4`
target_remote_host=`drush sa $target_alias | grep remote-host | cut -d"'" -f4`
target="$target_remote_user@$target_remote_host"

cd $source_root

echo "Enabling maintenance mode"
echo
#drush -l $source_site.factory.alshaya.com sset system.maintenance_mode TRUE

echo
echo "Syncing files with target env for $source_site"
source_files_folder=`drush -l $source_site.factory.alshaya.com status | grep Public | cut -d":" -f 2 | tr -d ' ' | tr -d '\n'`
search="$source_files_folder"
echo "Source folder $source_files_folder"

target_files_folder=`ssh -t $target "cd $target_root; drush -l $target_site.factory.alshaya.com status | grep 'Site path' | cut -d":" -f 2 | tr -d ' ' | tr -d '\n'"`
replace="$target_files_folder/files"
target_files_folder="$target_root/$target_files_folder"
echo "Target folder $target_files_folder"

screen -S rsync_${source_site}_${target_site} -dm bash -c "rsync -auv $source_files_folder $target:$target_files_folder"

echo
echo "Dumping database..."
mkdir -p /tmp/migrate

echo
echo "Dumping databases for $source_site"
drush -l $source_site.factory.alshaya.com sql-dump --result-file=/tmp/migrate/$source_site.sql --skip-tables-key=common

echo "Replacing site identifier in database search: $search, replace: $replace"
echo "s#$search#$replace#g" > /tmp/migrate/$source_site.sed
sed -i -f /tmp/migrate/$source_site.sed /tmp/migrate/$source_site.sql

echo
echo "Copying the dump to $target_env env..."
ssh $target 'mkdir -p /tmp/migrate'
scp /tmp/migrate/* $target:/tmp/migrate/

echo
echo "Clearing caches for $target_site"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com cr"

echo
echo "Droppping and importing database again for $target_site"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com sql-drop -y; drush -l $target_site.factory.alshaya.com sql-cli < /tmp/migrate/$source_site.sql"

echo
echo "Clearing caches for $target_site"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com cr"

echo
echo "Update simple_oauth settings: $target_simple_oauth"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com cset simple_oauth.settings public_key '${target_simple_oauth}alshaya_acm.pub' -y"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com cset simple_oauth.settings private_key '${target_simple_oauth}alshaya_acm' -y"

echo
echo "Update Acquia Subscription"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com ev \"(new \Drupal\acquia_connector\Subscription())->update();\""

echo
echo "Put copy online"
ssh $target "cd $target_root; drush -l $target_site.factory.alshaya.com sset system.maintenance_mode FALSE"

echo
echo "Removing temp directories for sql dumps in source and target envs"
rm -rf /tmp/migrate
ssh $target 'rm -rf /tmp/migrate'

echo

