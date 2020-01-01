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

echo
echo "Syncing files with target env for $source_site"
source_files_folder=`drush -l $source_site.factory.alshaya.com status | grep Public | cut -d":" -f 2 | tr -d ' ' | tr -d '\n'`
echo "Source folder $source_files_folder"
target_files_folder=`ssh -t $target "cd $target_root; drush -l $target_site.factory.alshaya.com status | grep 'Site path' | cut -d":" -f 2 | tr -d ' ' | tr -d '\n'"`
target_files_folder="$target_root/$target_files_folder"
echo "Target folder $target_files_folder"

rsync -auv $source_files_folder $target:$target_files_folder

echo
