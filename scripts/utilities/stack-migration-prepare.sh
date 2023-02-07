#!/bin/bash
#
# This script does rsync for files of a site between stacks.
#
# ./scripts/utilities/stack-migration-prepare.sh alshaya.01live vskw alshaya2.02live vskw2

source_env="$1"
source_site="$2"
target_env="$3"
target_site="$4"

# Move to docroot directory from home.
drush_directory="/var/www/html/${source_env}/docroot"

if [[ -z "$source_env" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh alshaya.01live vskw alshaya2.02live vskw2"
  exit
fi

if [[ -z "$source_site" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh alshaya.01live vskw alshaya2.02live vskw2"
  exit
fi

if [[ -z "$target_env" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh alshaya.01live vskw alshaya2.02live vskw2"
  exit
fi

if [[ -z "$target_site" ]]; then
  echo "Usage: ./scripts/utilities/stack-migration-prepare.sh SOURCE_ENV SOURCE_SITE_CODE TARGET_ENV TARGET_SITE_CODE"
  echo "Example: ./scripts/utilities/stack-migration-prepare.sh alshaya.01live vskw alshaya2.02live vskw2"
  exit
fi

cd $drush_directory

# Remove trailing numbers to get exact site code.
site_code=${source_site//[0-9]/}

# Remove the last two characters which are always the country code.
brand_code=${site_code%??}

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

source_root=`drush sa $source_alias | grep root | head -1 | awk '{print $2}'`
target_root=`drush sa $target_alias | grep root | head -1 | awk '{print $2}'`
target_remote_user=`drush sa $target_alias | grep user | awk '{print $2}'`
target_remote_host=`drush sa $target_alias | grep host | awk '{print $2}'`
target="$target_remote_user@$target_remote_host"
target_stack=`drush sa $target_alias | grep ac-site | awk '{print $2}'`

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
echo "Copying settings folder from source stack to target stack"
scp -r ~/settings $target:/home/$target_stack/

echo
echo "Copying apple pay folder from source stack to target stack"
scp -r ~/apple-pay-resources $target:/home/$target_stack/

echo
