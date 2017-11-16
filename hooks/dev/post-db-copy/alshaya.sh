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
echo "$site.$target_env: Received copy of database $db_name from $source_env."

# Check status once so hook_drush_command_alter is triggered.
drush8 @$site.$target_env --uri=$uri status

# Now clean all data.
drush8 @$site.$target_env --uri=$uri clean-synced-data -y
drush8 @$site.$target_env --uri=$uri sync-commerce-cats
drush8 @$site.$target_env --uri=$uri sync-commerce-product-options
drush8 @$site.$target_env --uri=$uri sync-commerce-products en 30 -y
drush8 @$site.$target_env --uri=$uri sync-commerce-products ar 15 -y

# Now we need to wait for products to finish loading for promotions to get applied
# Which we can do here but for sure it will be done in cron jobs.
# @TODO: Sync stores - will this be applicable even in non transac?
drush8 @$site.$target_env --uri=$uri alshaya-api-sync-stores
