uri="$1"

echo "Syncing product categories."
drush --uri=$uri sync-commerce-cats

echo "Syncing product options."
drush --uri=$uri sync-options

echo "Syncing products."
drush --uri=$uri acdsp 1000

echo "Syncing stores."
drush --uri=$uri sync-stores

echo "Syncing promotions."
drush --uri=$uri sync-commerce-promotions

echo "Running queues to attach and detach promotions to products."
drush --uri=$uri queue-run acq_promotion_attach_queue
drush --uri=$uri queue-run acq_promotion_detach_queue

echo "Marking all items for reindex."
drush --uri=$uri sapi-i
