#!/bin/sh
#
# Cloud Hook: post-db-copy
#
# The post-db-copy hook is run whenever you use the Workflow page to copy a
# database from one environment to another. See ../README.md for
# details.
#
# Usage: post-db-copy site target-env db-role source-env

site="$1"
target_env="$2"
db_role="$3"
source_env="$4"

# You need the URI of the site factory website in order for drush to target that
# site. Without it, the drush command will fail. The uri.php file below will
# locate the URI based on the site, environment and db role arguments.
uri=`/usr/bin/env php /mnt/www/html/$site.$target_env/hooks/acquia/uri.php $site $target_env $db_role`

# Print a statement to the cloud log.
echo "$site.$target_env: Received copy of database $db_role from $source_env."

# Check status once so hook_drush_command_alter is triggered.
drush8 @$site.$target_env --uri=$uri status

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

timestamp=$(date +%s)
drush8 @$site.$target_env --uri=$uri sql-dump | gzip > ~/$target_env/post_db_copy_$timestamp.sql
