#!/bin/bash
#
# This script clean production data from a specific site, synchronize the
# commerce data with the appropriate Magento and take database dump
# for later restore.

target_env="$1"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

site_code="$2"

# Move to proper directory to get access to drush acsf-tools commands.
cd `drush8 sa @alshaya.$target_env | grep root | cut -d"'" -f4`

# Check the given site_code exists.
exist=false
while IFS= read -r site
do

  if [ "$site" = "$site_code" ]
  then
    exist=true
  fi

done <<< "$(drush8 acsf-tools-list --fields)"

# If the given site_code is valid, launch the reset.
if [ $exist ]
then
  ./../hooks/scripts/reset-post-stage.sh $target_env $site_code
fi
