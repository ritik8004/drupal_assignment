#!/bin/sh

# "download" or "reuse".
arg1="$1"

# "test" or "uat" or "prod"
arg2="$2"

if [ $arg2 = "test" ]
then
  remote_alias="@alshaya.01test"
  remote_l_argument=" -l whitelabel16.test-alshaya.acsitefactory.com"
  origin_dir="sites/g/files/bndsjb6116test/files/"
  origin="https://whitelabel16.test-alshaya.acsitefactory.com"
elif [ $arg2 = "uat" ]
then
  remote_alias="@alshaya.01uat"
  remote_l_argument=" -l whitelabel1.uat-alshaya.acsitefactory.com"
  origin_dir="sites/g/files/bndsjb5371uat/files/"
  origin="https://whitelabel1.uat-alshaya.acsitefactory.com"
elif [ $arg2 = "prod" ]
then
  remote_alias="@alshaya.01live"
  remote_l_argument=" -l mckw.factory.alshaya.com"
  origin_dir="sites/g/files/bndsjb246/files/"
  origin="https://mckw.factory.alshaya.com"
else
  echo "Please provide valid env."
  exit 0
fi

local_archive="/tmp/alshaya_$arg2.sql"

ROOT=$(git rev-parse --show-toplevel 2> /dev/null)
path=$(dirname $0)

alias="@alshaya.local"
l_argument=""

# Check if we have an existing archive if we want to reuse existing one.
if [ $arg1 = "reuse" ]
then
  if [ ! -f $local_archive ]
  then
    echo "No existing archive found for $arg2"
    exit 0
  fi
  echo "Re-using downloaded database."
elif [ $arg1 = "download" ]
then
  echo "Downloading latest database from $arg2"
  drush $remote_alias $remote_l_argument sql-dump > $local_archive
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
echo "Installing database from $arg2 env"
drush $alias $l_argument sql-cli < $local_archive

# Update configs for oauth
echo "Updating config for oauth."
drush $alias $l_argument cset simple_oauth.settings private_key "/var/www/alshaya/box/alshaya_acm" -y
drush $alias $l_argument cset simple_oauth.settings public_key "/var/www/alshaya/box/alshaya_acm.pub" -y

# Update configs for autologout
echo "Updating config for autologout."
drush $alias $l_argument cset autologout.settings timeout 86400 -y
drush $alias $l_argument cset autologout.settings max_timeout 86400 -y

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
if [ -d "$ROOT/docroot/modules/development/stage_file_proxy" ]
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
  sh "$path/install-site-dev.sh" "$alias" "$l_argument" "$arg2"
fi
