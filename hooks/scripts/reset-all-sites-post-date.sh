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

# Move to proper directory to get access to drush acsf-tools commands.
cd `drush8 sa @alshaya.$target_env | grep root | cut -d"'" -f4`

# Get the list of all site names of the factory.
sites=$(drush8 acsf-tools-list --fields)

./../hooks/scripts/reset-post-stage.sh $target_env $sites