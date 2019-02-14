#!/bin/bash
#
# This script aims to be executed after a fresh installation of a site on ACSF.
# It will take care of the branding, creating the default users and
# synchronize the content with MDC.
#
# ./scripts/setup/setup-fresh-site.sh "01dev" "hmkw-dev.factory.alshaya.com" "hm" "kw"
#

target_env="$1"
site="$2"
brand_code="$3"
country_code="$4"

slack=""

## Delete the previous site-install logs. We don't do it at the end of this
## script so it can be checked later in case an issue is discovered.
rm $HOME/site-install.log

cd `drush sa | grep root | cut -d"'" -f4`

## Log the arguments and site status.
echo "Starting post-install operation on $site with $brand_code brand code and $country_code country code." &>> $HOME/site-install.log
drush -l $site status &>> $HOME/site-install.log

drush -l $site sqlq "select * from taxonomy_term_data limit 0, 1;" &>> /tmp/site-install.log
drush -l $site sqlq "select * from key_value limit 0, 1;" &>> /tmp/site-install.log

## Enable brand and country modules.
drush -l $site apdi --brand_module="alshaya_$brand_code" --country_code="$country_code" &>> $HOME/site-install.log

# Get the installed profile on the given site.
profile="$(drush -l $site php-eval 'echo \Drupal::installProfile();')"

# Next operations are for transac sites only.
if [ $profile = "alshaya_transac" ]; then

  ## Next operations are not done on production.
  if [ $target_env != "01live" -o $target_env != "01update" ]; then
    ## Create default users (not on production).
    drush -l $site upwd "Site factory admin" "AlShAyAU1@123" &>> $HOME/site-install.log

    drush -l $site user-create siteadmin --mail="user3+admin@example.com" --password="AlShAyAU1admin" &>> $HOME/site-install.log
    drush -l $site user-add-role "administrator" siteadmin &>> $HOME/site-install.log

    drush -l $site user-create webmaster --mail="user3+webmaster@example.com" --password="AlShAyAU1webmaster" &>> $HOME/site-install.log
    drush -l $site user-add-role "webmaster" webmaster &>> $HOME/site-install.log

    drush -l $site cr &>> $HOME/site-install.log

    ## Remove shield.
    drush -l $site pm-uninstall shield -y &>> $HOME/site-install.log

    ## Synchronize commerce data.
    conductor=$(drush -l $site cget acq_commerce.conductor url --format=string)

    if [ ! -z "$conductor" ]; then
      echo "Synchronizing commerce data." &>> $HOME/site-install.log
      drush -l $site sync-commerce-product-options &>> $HOME/site-install.log
      drush -l $site sync-commerce-cats &>> $HOME/site-install.log

      drush -l $site sync-areas &>> $HOME/site-install.log

      drush -l $site sync-stores &>> $HOME/site-install.log

      ## Synchronize products.
      drush -l $site acdsp 1000 &>> $HOME/site-install.log

      ## Synchronize promotions.
      drush -l $site acspm &>> $HOME/site-install.log
      drush -l $site queue-run acq_promotion_attach_queue &>> $HOME/site-install.log
      drush -l $site queue-run acq_promotion_detach_queue &>> $HOME/site-install.log
    else
      echo "Commerce data sync have been by-passed given the conductor settings are not set." &>> $HOME/site-install.log
      slack="$slack \n*Commerce data sync have been by-passed given the conductor settings are not set.*"
    fi
  else
    echo "Commerce data sync have been by-passed given we are on production environment." &>> $HOME/site-install.log
    slack="$slack \n*Commerce data sync have been by-passed given we are on production environment.*"
  fi
fi

## Push the post-install logs on Slack channel.
FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  . $HOME/slack_settings
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" $site has been setup with following arguments: \nBrand code: *$brand_code* \nCountry code: *$country_code* $slack \nIt is time to run Selenium script if any.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
fi
