uri="$1"

# Truncate acq_sku% tables.
# @TODO: Confirm no SKU blocking data are remaining (workflow, files, ...)."
echo "Truncate acq_sku% tables to speed up commerce data cleaning."
drush --uri=$uri sqlq "SHOW TABLES LIKE 'acq_sku%'" | xargs -I acq_sku_tables drush --uri=$uri sqlq "TRUNCATE table acq_sku_tables"

while :
do
  drush --uri=$uri clean-synced-data -y
  to_clean=$(drush --uri=$uri acq_sku:get-item-delete-count)
  echo "$to_clean items to clean"

  if [ $to_clean == "0" ]
  then
    break
  fi
done

# Clear permanent cache for products to avoid issues with same SKUs on prod.
echo "Clear permanent caches."
drush --uri=$uri pcb:flush-all -y

echo "Enable the search api indexes again."
drush --uri=$uri search-api-enable-all

echo "Clearing all indexed data."
drush --uri=$uri search-api-clear

echo "Reset purge queue"
drush --uri=$uri p-queue-empty

echo "Delete all active sessions so we can have fresh carts after resetting data"
drush --uri=$uri sqlq "DELETE FROM sessions;"

echo "Delete all product media files from database"
drush --uri=$uri sqlq "DELETE FROM file_usage WHERE fid IN (SELECT fid from file_managed WHERE uri LIKE 'public://media/%');"
drush --uri=$uri sqlq "DELETE FROM file_usage WHERE fid IN (SELECT fid from file_managed WHERE uri LIKE 'public://assets/%');"
drush --uri=$uri sqlq "DELETE FROM file_usage WHERE fid IN (SELECT fid from file_managed WHERE uri LIKE 'public://assets-lp/%');"
drush --uri=$uri sqlq "DELETE FROM file_managed WHERE uri LIKE 'public://media/%';"
drush --uri=$uri sqlq "DELETE FROM file_managed WHERE uri LIKE 'public://assets/%';"
drush --uri=$uri sqlq "DELETE FROM file_managed WHERE uri LIKE 'public://assets-lp/%';"

files_dir="$(drush --uri=$uri php-eval 'echo \Drupal::service("file_system")->realpath("public://");')"

echo "Creating directory 'todelete' to move all files we want to delete inside it."
mkdir -p "$files_dir/todelete"

echo "Moving product media files directory inside 'todelete'"
if [ -d "$files_dir/media" ]
then
 mv "$files_dir/media" "$files_dir/todelete/media"
fi
if [ -d "$files_dir/assets" ]
then
 mv "$files_dir/assets" "$files_dir/todelete/assets"
fi
if [ -d "$files_dir/assets-shared" ]
then
 mv "$files_dir/assets-shared" "$files_dir/todelete/assets-shared"
fi
if [ -d "$files_dir/assets-lp" ]
then
 mv "$files_dir/assets-lp" "$files_dir/todelete/assets-lp"
fi
if [ -d "$files_dir/assets-lp-shared" ]
then
 mv "$files_dir/assets-lp-shared" "$files_dir/todelete/assets-lp-shared"
fi


echo "Moving styles directory inside 'todelete'"
if [ -d "$files_dir/styles" ]
then
  mv "$files_dir/styles" "$files_dir/todelete/styles"
fi

echo "Re-creating empty styles directory"
mkdir "$files_dir/styles"
