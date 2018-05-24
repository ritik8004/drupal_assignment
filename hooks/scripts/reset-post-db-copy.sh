# Usage: reset-post-db-copy.sh site target-env uri
# Example: /var/www/html/alshaya.01test/hooks/scripts/reset-post-db-copy.sh alshaya 01test mckw-test.factory.alshaya.com

site="$1"
target_env="$2"
uri="$3"

SECONDS=0
total_seconds=0

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

# Check status once so hook_drush8_command_alter is triggered.
drush8 @$site.$target_env --uri=$uri status

# Clear cache, we want to avoid fatal errors because of updated services.
drush8 @$site.$target_env --uri=$uri cr

# Enable developer modules, we are going to use this script only on non-prod envs.
drush8 @$site.$target_env --uri=$uri en -y dblog views_ui features_ui restui

echo "Disabling all search api indexes."
drush8 @$site.$target_env --uri=$uri search-api-disable-all

# Now clean all data.
SECONDS=0

# Truncate acq_sku% tables.
# @TODO: Confirm no SKU blocking data are remaining (workflow, files, ...)."
echo "Truncate acq_sku% tables to speed up commerce data cleaning."
drush8 @$site.$target_env --uri=$uri sqlq "SHOW TABLES LIKE 'acq_sku%'" | xargs -I acq_sku_tables drush8 @$site.$target_env --uri=$uri sqlq "TRUNCATE table acq_sku_tables"

while :
do
  drush8 @$site.$target_env --uri=$uri clean-synced-data -y
  to_clean=$(drush8 @$site.$target_env --uri=$uri sqlq "select count(*) from (select nid from node where type in ('acq_product', 'acq_promotion', 'store') union select id from acq_sku union select tid from taxonomy_term_data where vid='acq_product_category') as tmp")
  echo "$to_clean items to clean"

  if [ $to_clean == "0" ]
  then
    break
  fi
done

let "minutes=(SECONDS%3600)/60"
let "seconds=(SECONDS%3600)%60"
echo "Data cleanup completed in $minutes minute(s) and $seconds second(s)."

let "total_seconds+=SECONDS"
SECONDS=0

echo "Enable the search api indexes again."
drush8 @$site.$target_env --uri=$uri search-api-enable-all

echo "Clearing all indexed data."
drush8 @$site.$target_env --uri=$uri search-api-clear

drush8 @$site.$target_env --uri=$uri sync-commerce-cats
drush8 @$site.$target_env --uri=$uri sync-commerce-product-options

# Disable shield for product push to work.
drush8 @$site.$target_env --uri=$uri pm-uninstall -y shield

drush8 @$site.$target_env --uri=$uri sync-commerce-products en 10 -y
drush8 @$site.$target_env --uri=$uri sync-commerce-products ar 5 -y
drush8 @$site.$target_env --uri=$uri sync-stores

# Now wait for SKUs to be loaded.
new_count="0"
while :
do
  sleep 60
  old_count=$new_count
  new_count=$(drush8 @$site.$target_env --uri=$uri sqlq "select count(*) from acq_sku")
  echo "Before sleep $old_count - after sleep $new_count"

  if [ $old_count != "0" -a $old_count == $new_count ]
  then
    break
  fi
done

drush8 @$site.$target_env --uri=$uri sync-commerce-promotions
# Re-index all indexes.
drush8 @$site.$target_env --uri=$uri sapi-i
drush8 @$site.$target_env --uri=$uri queue-run acq_promotion_attach_queue
drush8 @$site.$target_env --uri=$uri queue-run acq_promotion_detach_queue

let "minutes=(SECONDS%3600)/60"
let "seconds=(SECONDS%3600)%60"
echo "Data import completed in $minutes minute(s) and $seconds second(s)."
let "total_seconds+=SECONDS"
SECONDS=0

let "total_seconds+=SECONDS"
let "minutes=(total_seconds%3600)/60"
let "seconds=(total_seconds%3600)%60"

echo "Entire script completed in $minutes minute(s) and $seconds second(s)."
