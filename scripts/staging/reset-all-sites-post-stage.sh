#!/bin/bash
#
# This script clean production data from all sites, synchronize the commerce
# data with the appropriate Magento and take database dump for later restore.

target_env="$1"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

# Move to proper directory to get access to drush9 acsf-tools commands.
cd /var/www/html/alshaya$target_env/docroot

# Get the list of all site names of the factory.
sites=$(drush acsf-tools-list --fields)

echo "Deleting all existing pre-stage database dump for all sites."
rm -rf ~/backup/$target_env/pre-stage/*

echo "Deleting existing post-stage database dump for all sites."
rm -rf ~/backup/$target_env/post-stage/*

./../scripts/staging/reset-post-stage.sh $target_env "$sites"