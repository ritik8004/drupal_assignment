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

# Get the environment without the "01" prefix.
env=${target_env:2}

# Get the list of all site names of the factory.
echo "Fetching the list of sites."
sites=$(drush8 acsf-tools-list --fields)

# Loop on all site names.
echo "$sites" | while IFS= read -r site
do
  # Get the installed profile on the given site.
  profile="$(drush8 -l $site.$env-alshaya.acsitefactory.com php-eval 'echo drupal_get_profile();')"

  # For transac sites, we launch the commerce clean.
  if [ $profile = "alshaya_transac" ]
  then
    echo "Execute data commerce clean on $site."
    ./../hooks/scripts/reset-post-db-copy.sh "alshaya" $target_env $site.$env-alshaya.acsitefactory.com
  fi

done

# Take dumps of all the sites.
drush8 acsf-tools-dump --result-folder=~/backup/post-stage --gzip
