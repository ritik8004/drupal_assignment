#!/bin/bash
#
# This script aims to be executed after a fresh installation of a site on ACSF.
# It will take care of the branding, creating the default users and
# synchronize the content with MDC.

target_env="$1"
site="$2"
brand_code="$3"
country_code="$4"

echo "Starting site setup with following arguments:"
echo "\nEnvironment: $target_env"
echo "\nDomain: $site"
echo "\nBrand code: $brand_code"
echo "\nCountry code: $country_code"
echo "\n"

drush8 @alshaya.$target_env -l $site status;

echo "\nEnabling brand and country modules."
drush8 @alshaya.$target_env -l $site apdi --brand_module="alshaya_$brand_code" --country_code="$country_code"

if [ $target_env != "01live" -o $target_env != "01update" ]
then
  echo "\nConfiguring default users."
  drush8 @alshaya.$target_env -l $site upwd "Site factory admin" --password="AlShAyAU1@123"

  drush8 @alshaya.$target_env -l $site user-create siteadmin --mail="user3+admin@example.com" --password=AlShAyAU1admin;
  drush8 @alshaya.$target_env -l $site user-add-role "administrator" --name=siteadmin;

  drush8 @alshaya.$target_env -l $site user-create webmaster --mail="user3+webmaster@example.com" --password=AlShAyAU1webmaster;
  drush8 @alshaya.$target_env -l $site user-add-role "webmaster" --name=webmaster;
fi

drush8 @alshaya.$target_env -l $site cr;

echo "\nPreparing site to sync commerce data."
drush8 @alshaya.$target_env -l $site pm-uninstall shield -y;
drush8 @alshaya.$target_env -l $site en basic_auth -y;
drush8 @alshaya.$target_env -l $site sqlq "update users_field_data set name='admin' where name='Site Factory admin'";
drush8 @alshaya.$target_env -l $site upwd "admin" --password="AlShAyAU1";

echo "\nSynchronizing commerce data."
drush8 @alshaya.$target_env -l $site sync-commerce-product-options;
drush8 @alshaya.$target_env -l $site sync-commerce-cats;

drush8 @alshaya.$target_env -l $site sync-areas;

drush8 @alshaya.$target_env -l $site sync-stores;

drush8 @alshaya.$target_env -l $site acsp en 5 -y;
drush8 @alshaya.$target_env -l $site acsp ar 3 -y;

sleepd=15
max_loop_count=180
new_count="0"
loop_count=0
while :
do
  sleep $sleepd
  old_count=$new_count
  new_count=$(drush8 @alshaya.$target_env -l $site sqlq "select count(*) from acq_sku")
  echo "There is now $new_count SKUs, there was $old_count SKUs $sleepd seconds ago."

  if [ $old_count != "0" -a $old_count == $new_count ]
  then
    echo "SKUs count has not changed since $sleepd seconds. Considering the sync done."
    break
  fi

  if [ $max_loop_count == $loop_count ]
  then
    echo "We have been waiting for SKUs to sync too long now. There is probably an issue with sync on $site. Break!"
    break
   fi

  echo "Waiting $sleepd seconds for more SKUs to come."
  loop_count=$((loop_count+1))
done

drush8 @alshaya.$target_env -l $site acspm;
drush8 @alshaya.$target_env -l $site queue-run acq_promotion_attach_queue;
drush8 @alshaya.$target_env -l $site queue-run acq_promotion_detach_queue;

echo "\nCONGRATULATIONS. The site is accessible at $site."