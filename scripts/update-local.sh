#!/bin/sh

# Usage: scripts/update-local.sh "site" "env" "mode"

# "mckw" or "hmkw".
site="$1"

# "01dev" or "01test" or "01uat" or "01live".
env="$2"

# "reuse" or "download".
mode="$3"

remote_alias="@alshaya.$env"

# Get acsf info from remote.
remote_info=$(drush $remote_alias acsf-tools-list --fields=name,domains)

remote_db_role=$(echo "$remote_info" | php scripts/get-acsf-info.php $site db_role)
remote_url=$(echo "$remote_info" | php scripts/get-acsf-info.php $site url)

# Below stuff is used for stage file proxy.
origin_dir="sites/g/files/$remote_db_role/files/"
origin="https://$remote_url"

ROOT=$(git rev-parse --show-toplevel 2> /dev/null)
path=$(dirname $0)

alias="@alshaya.local"
l_argument="-l local.alshaya-$site.com"

# Check if we have an existing archive if we want to reuse existing one.
if [ $mode = "reuse" ]
then
  echo "Remote URL: $remote_url"

  local_archive="../tmp/alshaya_${site}_${env}.sql"
  echo "Local archive: $local_archive"

  if [ ! -f $local_archive ]
  then
    echo "No existing archive found for $env"
    exit 0
  fi
  echo "Re-using downloaded database."
elif [ $mode = "download" ]
then
  sh "$path/download-db.sh" "$site" "$env"

  if [ ! -f $local_archive ]
  then
    echo "Downloading database failed, please check for log messages above."
    exit 0
  fi
else
  echo "Please provide valid argument to download or reuse db."
  exit 0
fi

# Drop the current database.
echo "Dropping local database"
drush $alias $l_argument sql-drop --y

# Clear memcache to avoid cache issues during update.
echo "Clearing memcache"
drush $alias $l_argument ssh "sudo service memcached restart"

# Install the dump.
echo "Installing database from $env env"
drush $alias $l_argument sql-cli < $local_archive

# Install local only modules
drush $alias $l_argument en -y dblog views_ui features_ui restui alshaya_search_local_search

# Uninstall cloud only modules
drush $alias $l_argument pmu -y purge alshaya_search_acquia_search acquia_search acquia_connector

# Clear memcache to avoid cache issues during update.
echo "Clearing memcache"
drush $alias $l_argument ssh "sudo service memcached restart"

# Save server config again.
drush $alias $l_argument php-eval "alshaya_config_install_configs(['search_api.server.acquia_search_server'], 'alshaya_search');"

# Reset indexed data and reindex 5000 for search page to work.
drush $alias $l_argument search-api-clear acquia_search_index -y
drush $alias $l_argument search-api-index acquia_search_index 5000 -y

# Update super admin email to local default.
echo "Update super admin email to no-reply@acquia.com."
drush $alias $l_argument sqlq "update users_field_data set mail = 'no-reply@acquia.com', name = 'admin' where uid = 1"

# Change user 1 password.
echo "Resetting super admin password to admin"
drush $alias $l_argument user-password admin --password="admin"

# Unblock user 1
echo "Unblocking super admin user"
drush $alias $l_argument uublk --name=admin

# Set stage file proxy settings if module available
if [ -d "$ROOT/docroot/modules/contrib/stage_file_proxy" ]
then
  drush $alias $l_argument en stage_file_proxy -y
  drush $alias $l_argument cset stage_file_proxy.settings origin "$origin" -y
  drush $alias $l_argument cset stage_file_proxy.settings origin_dir "$origin_dir" -y
  drush $alias $l_argument cset stage_file_proxy.settings verify 0 -y
fi

# Clear the cache.
echo "Clear cache"
drush $alias $l_argument cr

# Execute dev steps.
if [ -f "$path/install-site-dev.sh" ]
then
  echo "Execute dev steps."
  sh "$path/install-site-dev.sh" "$alias" "$l_argument" "$env"
fi
