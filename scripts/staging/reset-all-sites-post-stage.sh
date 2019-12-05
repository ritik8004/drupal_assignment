#!/bin/bash
#
# This script clean production data from all sites, synchronize the commerce
# data with the appropriate Magento and take database dump for later restore.

target_env=${AH_SITE_ENVIRONMENT}

# Get the environment without the "01" prefix.
env=${target_env:2}

if [ $env = "live" -o $env = "update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

# Move to proper directory to get access to drush9 acsf-tools commands.
cd /var/www/html/${AH_SITE_NAME}/docroot

# Get the list of all site names of the factory.
sites=$(drush acsf-tools-list --fields)

echo "Deleting all existing pre-stage database dump for all sites."
mkdir -p ~/backup/$target_env/pre-stage
rm -rf ~/backup/$target_env/pre-stage/*

echo "Deleting existing post-stage database dump for all sites."
mkdir -p ~/backup/$target_env/post-stage
rm -rf ~/backup/$target_env/post-stage/*

./../scripts/staging/reset-post-stage.sh "$sites"
