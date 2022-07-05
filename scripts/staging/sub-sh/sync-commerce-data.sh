uri="$1"

echo "Syncing product categories."
drush --uri=$uri sync-commerce-cats -y

echo "Syncing product options."
drush --uri=$uri sync-options

echo "Syncing products."
drush --uri=$uri acdsp 1000

echo "Syncing stores."
drush --uri=$uri sync-stores

echo "Syncing promotions."
drush --uri=$uri sync-and-process-promotions --types=cart
drush --uri=$uri sync-and-process-promotions --types=category

echo "Running queues to attach catalogue promotions to products."
drush --uri=$uri queue-run acq_promotion_attach_queue

echo "Index all items."
drush --uri=$uri ev "\Drupal::service('alshaya_acm_product.product_queue_utility')->queueAllProducts();"
