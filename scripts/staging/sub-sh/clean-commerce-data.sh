uri="$1"

# Truncate acq_sku% tables.
# @TODO: Confirm no SKU blocking data are remaining (workflow, files, ...)."
echo "Truncate acq_sku% tables to speed up commerce data cleaning."
drush --uri=$uri sqlq "SHOW TABLES LIKE 'acq_sku%'" | xargs -I acq_sku_tables drush --uri=$uri sqlq "TRUNCATE table acq_sku_tables"

while :
do
  drush --uri=$uri clean-synced-data -y
  to_clean=$(drush --uri=$uri sqlq "select count(*) from (select nid from node where type in ('acq_product', 'acq_promotion', 'store') union select id from acq_sku union select tid from taxonomy_term_data where vid='acq_product_category') as tmp")
  echo "$to_clean items to clean"

  if [ $to_clean == "0" ]
  then
    break
  fi
done

echo "Clearing permanent cache for products to avoid issues with same SKUs in different environments."
drush --uri=$uri pcbf alshaya_product

echo "Enable the search api indexes again."
drush --uri=$uri search-api-enable-all

echo "Clearing all indexed data."
drush --uri=$uri search-api-clear

echo "Reset purge queue"
drush --uri=$uri p-queue-empty

echo "Delete all active sessions so we can have fresh carts after resetting data"
drush --uri=$uri sqlq "DELETE FROM sessions;"
