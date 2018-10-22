uri="$1"

echo "Syncing product categories."
drush8 --uri=$uri sync-commerce-cats

echo "Syncing product options."
drush8 --uri=$uri sync-commerce-product-options

echo "Syncing products."
drush8 --uri=$uri acdsp 1000

echo "Syncing stores."
drush8 --uri=$uri sync-stores

echo "Syncing promotions."
drush8 --uri=$uri sync-commerce-promotions

echo "Running queues to attach and detach promotions to products."
drush8 --uri=$uri queue-run acq_promotion_attach_queue
drush8 --uri=$uri queue-run acq_promotion_detach_queue

echo "Marking all items for reindex."
drush8 --uri=$uri sapi-i
