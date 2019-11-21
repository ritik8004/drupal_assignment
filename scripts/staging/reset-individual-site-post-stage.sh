#!/bin/bash
#
# This script clean production data from a specific site, synchronize the
# commerce data with the appropriate Magento and take database dump
# for later restore.

target_env=${AH_SITE_ENVIRONMENT}

# Get the environment without the "01" prefix.
env=${target_env:2}

if [ $env = "live" -o $env = "update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

site_code="$1"

# Move to proper directory to get access to drush9 acsf-tools commands.
cd /var/www/html/${AH_SITE_NAME}/docroot

# Check the given site_code exists.
exist=false
while IFS= read -r site
do

  if [ "$site" = "$site_code" ]
  then
    exist=true
  fi

done <<< "$(drush acsf-tools-list --fields)"

# If the given site_code is valid, launch the reset.
if [ $exist ]
then
  ./../scripts/staging/reset-post-stage.sh $site_code
fi
