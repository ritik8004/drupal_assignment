#!/usr/bin/screen -d -m -S resetData /bin/bash
#
# Usage: reset-post-db-copy.sh site target-env uri

site="$1"
target_env="$2"
uri="$3"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

# Check status once so hook_drush_command_alter is triggered.
drush8 @$site.$target_env --uri=$uri status

# Clear cache, we want to avoid fatals because of updated services.
drush8 @$site.$target_env --uri=$uri cr

# Enable developer modules, we are going to use this script only on non-prod envs.
drush8 @$site.$target_env --uri=$uri en -y dblog views_ui features_ui restui

# Now clean all data.
drush8 @$site.$target_env --uri=$uri clean-synced-data -y
drush8 @$site.$target_env --uri=$uri sync-commerce-cats
drush8 @$site.$target_env --uri=$uri sync-commerce-product-options
drush8 @$site.$target_env --uri=$uri sync-commerce-products en 30 -y
drush8 @$site.$target_env --uri=$uri sync-commerce-products ar 15 -y
drush8 @$site.$target_env --uri=$uri alshaya-api-sync-stores

# Now wait for SKUs to be loaded.
while :
do
  sleep 60
  SKUCOUNT=$(drush8 @$site.$target_env --uri=$uri sqlq "select count(*) from acq_sku_field_data")

  if [ $SKUCOUNT -gt 20000 ]
  then
    break
  fi
done

drush8 @$site.$target_env --uri=$uri sync-commerce-promotions
drush8 @$site.$target_env --uri=$uri queue-run acq_promotion_attach_queue
drush8 @$site.$target_env --uri=$uri queue-run acq_promotion_detach_queue

# Save the dump for later use and use in local.
timestamp=$(date +%s)
db_prefix=${uri//[-._]/}
drush8 @$site.$target_env --uri=$uri sql-dump | gzip > ~/$target_env/post_db_copy_${db_prefix}_${timestamp}.sql.gz
