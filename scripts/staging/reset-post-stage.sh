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

sites="$2"

if [ -z "$sites" ]
then
  echo "A list of sites to reset is required. You probably need to use wrapper scripts reset-all-sites-post-stage.sh or reset-individual-sites-post-stage.sh instead."
  exit
fi

# Get the environment without the "01" prefix.
env=${target_env:2}

# Move to proper directory to get access to drush9 acsf-tools commands.
cd /var/www/html/alshaya$target_env/docroot

echo "Starting post stage reset process on $sites."

# Take dumps of all the given sites before start.
echo "$sites" | while IFS= read -r site
do
  echo "Taking database dump of $site before doing anything."
  drush sql-dump -l $site.$env-alshaya.acsitefactory.com --result-file=~/backup/$target_env/pre-stage/$site.sql --gzip
done

###### CLEAR + SYNC.
echo "$sites" | while IFS= read -r site
do
  # Get the installed profile on the given site.
  profile="$(drush -l $site.$env-alshaya.acsitefactory.com php-eval 'echo drupal_get_profile();')"

  # For transac sites, we launch the commerce clean.
  if [ $profile = "alshaya_transac" ]
  then
    echo "Cleaning and syncing commerce data on $site."
    ./../scripts/staging/sub/prepare-site-for-reset.sh $site.$env-alshaya.acsitefactory.com
    ./../scripts/staging/sub/clean-commerce-data.sh $site.$env-alshaya.acsitefactory.com
    ./../scripts/staging/sub/sync-commerce-data.sh $site.$env-alshaya.acsitefactory.com
  fi

done

# Take dumps of all the given sites at the end.
echo "$sites" | while IFS= read -r site
do
  echo "Taking database dump of $site after process."
  drush sql-dump -l $site.$env-alshaya.acsitefactory.com --result-file=~/backup/$target_env/post-stage/$site.sql --gzip
done
