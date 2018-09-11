site="$1"
target_env="$2"
uri="$3"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

echo "Syncing product categories."
drush8 @$site.$target_env --uri=$uri sync-commerce-cats

echo "Syncing product options."
drush8 @$site.$target_env --uri=$uri sync-commerce-product-options

echo "Syncing products."
drush8 @$site.$target_env --uri=$uri acdsp 1000

echo "Syncing stores."
drush8 @$site.$target_env --uri=$uri sync-stores

echo "Syncing promotions."
drush8 @$site.$target_env --uri=$uri sync-commerce-promotions

echo "Running queues to attach and detach promotions to products."
drush8 @$site.$target_env --uri=$uri queue-run acq_promotion_attach_queue
drush8 @$site.$target_env --uri=$uri queue-run acq_promotion_detach_queue

echo "Marking all items for reindex."
drush8 @$site.$target_env --uri=$uri sapi-i
