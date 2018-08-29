#!/bin/bash
#
# This script aims to be executed after a fresh installation of a site on ACSF.
# It will take care of the branding, creating the default users and
# synchronize the content with MDC.

target_env="$1"
site="$2"
brand_code="$3"
country_code="$4"

slack=""

## Delete the previous site-install logs. We don't do it at the end of this
## script so it can be checked later in case an issue is discovered.
rm $HOME/site-install.txt

cd `drush8 sa @alshaya.$target_env | grep root | cut -d"'" -f4`

## Log the arguments and site status.
echo "Starting post-install operation on $site with $brand_code brand code and $country_code country code." &>> $HOME/site-install.txt
drush8 @alshaya.$target_env -l $site status &>> $HOME/site-install.txt

## Enable brand and country modules.
drush8 @alshaya.$target_env -l $site apdi --brand_module="alshaya_$brand_code" --country_code="$country_code" &>> $HOME/site-install.txt

## Create default users (not on production).
if [ $target_env != "01live" -o $target_env != "01update" ]
then
  drush8 @alshaya.$target_env -l $site upwd "Site factory admin" --password="AlShAyAU1@123" &>> $HOME/site-install.txt

  drush8 @alshaya.$target_env -l $site user-create siteadmin --mail="user3+admin@example.com" --password=AlShAyAU1admin &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site user-add-role "administrator" --name=siteadmin &>> $HOME/site-install.txt

  drush8 @alshaya.$target_env -l $site user-create webmaster --mail="user3+webmaster@example.com" --password=AlShAyAU1webmaster &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site user-add-role "webmaster" --name=webmaster &>> $HOME/site-install.txt
fi

drush8 @alshaya.$target_env -l $site cr &>> $HOME/site-install.txt

## Remove shield and configure sync.
drush8 @alshaya.$target_env -l $site pm-uninstall shield -y &>> $HOME/site-install.txt
drush8 @alshaya.$target_env -l $site en basic_auth -y &>> $HOME/site-install.txt
drush8 @alshaya.$target_env -l $site sqlq "update users_field_data set name='admin' where name='Site Factory admin'" &>> $HOME/site-install.txt
drush8 @alshaya.$target_env -l $site upwd "admin" --password="AlShAyAU1" &>> $HOME/site-install.txt

## Synchronize commerce data.
conductor=$(drush8 @alshaya.$target_env -l $site cget acq_commerce.conductor url --format=string)

if [ ! -z "$conductor" ]; then
  echo "Synchronizing commerce data." &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site sync-commerce-product-options &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site sync-commerce-cats &>> $HOME/site-install.txt

  drush8 @alshaya.$target_env -l $site sync-areas &>> $HOME/site-install.txt

  drush8 @alshaya.$target_env -l $site sync-stores &>> $HOME/site-install.txt

  ## Synchronize products.
  drush8 @alshaya.$target_env -l $site acsp en 5 -y &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site acsp ar 3 -y &>> $HOME/site-install.txt

  sleepd=15
  max_loop_count=180
  new_count="0"
  loop_count=0
  while :
  do
    sleep $sleepd
    old_count=$new_count
    new_count=$(drush8 @alshaya.$target_env -l $site sqlq "select count(*) from acq_sku")
    echo "There is now $new_count SKUs, there was $old_count SKUs $sleepd seconds ago." &>> $HOME/site-install.txt

    if [ $old_count != "0" -a $old_count == $new_count ]
    then
      echo "SKUs count has not changed since $sleepd seconds. Considering the sync done." &>> $HOME/site-install.txt
      break
    fi

    if [ $max_loop_count == $loop_count ]
    then
      echo "We have been waiting for SKUs to sync too long now. There is probably an issue with sync on $site. Break!" &>> $HOME/site-install.txt
      break
     fi

    echo "Waiting $sleepd seconds for more SKUs to come." &>> $HOME/site-install.txt
    loop_count=$((loop_count+1))
  done

  ## Synchronize promotions.
  drush8 @alshaya.$target_env -l $site acspm &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site queue-run acq_promotion_attach_queue &>> $HOME/site-install.txt
  drush8 @alshaya.$target_env -l $site queue-run acq_promotion_detach_queue &>> $HOME/site-install.txt
else
  echo "Commerce data sync have been by-passed given the conductor settings are not set." &>> $HOME/site-install.txt
  slack="$slack \n*Commerce data sync have been by-passed given the conductor settings are not set.*"
fi

## Push the post-install logs on Slack channel.
FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  . $HOME/slack_settings
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" $site has been setup with following arguments: \nBrand code: *$brand_code* \nCountry code: *$country_code* $slack \nIt is time to run Selenium script if any.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
fi
