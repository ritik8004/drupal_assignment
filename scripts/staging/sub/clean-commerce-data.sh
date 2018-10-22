uri="$1"

# Truncate acq_sku% tables.
# @TODO: Confirm no SKU blocking data are remaining (workflow, files, ...)."
echo "Truncate acq_sku% tables to speed up commerce data cleaning."
drush8 --uri=$uri sqlq "SHOW TABLES LIKE 'acq_sku%'" | xargs -I acq_sku_tables drush8 --uri=$uri sqlq "TRUNCATE table acq_sku_tables"

while :
do
  drush8 --uri=$uri clean-synced-data -y
  to_clean=$(drush8 --uri=$uri sqlq "select count(*) from (select nid from node where type in ('acq_product', 'acq_promotion', 'store') union select id from acq_sku union select tid from taxonomy_term_data where vid='acq_product_category') as tmp")
  echo "$to_clean items to clean"

  if [ $to_clean == "0" ]
  then
    break
  fi
done

echo "Enable the search api indexes again."
drush8 --uri=$uri search-api-enable-all

echo "Clearing all indexed data."
drush8 --uri=$uri search-api-clear

echo "Reset purge queue"
drush8 --uri=$uri p-queue-empty
