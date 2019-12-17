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

sites="$1"

if [ -z "$sites" ]
then
  echo "A list of sites to reset is required. You probably need to use wrapper scripts reset-all-sites-post-stage.sh or reset-individual-sites-post-stage.sh instead."
  exit
fi

# Move to proper directory to get access to drush9 acsf-tools commands.
cd /var/www/html/${AH_SITE_NAME}/docroot

echo "Starting post stage reset process on $sites."

# Take dumps of all the given sites before start.
echo "$sites" | while IFS= read -r site
do
  echo "Taking database dump of $site before doing anything."
  mkdir -p ~/backup/$target_env/pre-stage
  drush sql-dump -l $site.$env-alshaya.acsitefactory.com --result-file=~/backup/$target_env/pre-stage/$site.sql --gzip
done

# Run updb. In case of soft-stage, it is required to be sure next steps don't
# fail due to invalid code/database. In case of hard-stage, it does not have
# any impact given code is same as on prod environment.
echo "$sites" | while IFS= read -r site
do
  echo "Running database update on $site."
  drush -l $site.$env-alshaya.acsitefactory.com updb -y
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
    ./../scripts/staging/sub-sh/prepare-site-for-reset.sh $site.$env-alshaya.acsitefactory.com
    ./../scripts/staging/sub-sh/clean-commerce-data.sh $site.$env-alshaya.acsitefactory.com
    ./../scripts/staging/sub-sh/sync-commerce-data.sh $site.$env-alshaya.acsitefactory.com
  fi

done

# Take dumps of all the given sites at the end.
echo "$sites" | while IFS= read -r site
do
  echo "Taking database dump of $site after process."
  mkdir -p ~/backup/$target_env/post-stage
  drush sql-dump -l $site.$env-alshaya.acsitefactory.com --result-file=~/backup/$target_env/post-stage/$site.sql --gzip
done
