#!/bin/sh

# "download" or "reuse".
arg1="$1"

# "test" or "uat" or "prod"
arg2="$2"

if [ $arg2 = "dev" ]
then
  remote_user="alshaya.01dev"
  server="staging-1505.enterprise-g1.hosting.acquia.com"
  dir="/home/alshaya/01dev"
  origin_dir="sites/g/files/bndsjb246/files/"
  origin="https://mckw.dev-alshaya.acsitefactory.com"
elif [ $arg2 = "test" ]
then
  remote_user="alshaya.01test"
  server="staging-1505.enterprise-g1.hosting.acquia.com"
  dir="/home/alshaya/01test"
  origin_dir="sites/g/files/bndsjb246/files/"
  origin="https://mckw.test-alshaya.acsitefactory.com"
elif [ $arg2 = "uat" ]
then
  remote_user="alshaya.01uat"
  server="staging-1509.enterprise-g1.hosting.acquia.com"
  dir="/home/alshaya/01uat"
  origin_dir="sites/g/files/bndsjb5371uat/files/"
  origin="https://whitelabel1.uat-alshaya.acsitefactory.com"
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
  if [ ! -f $local_archive.gz ]
  then
    echo "No existing archive found for $arg2"
    exit 0
  fi
  echo "Re-using downloaded database."
elif [ $arg1 = "download" ]
then
  echo "Downloading latest database from $arg2"
  scp $remote_user@$server:$(ssh $remote_user@$server "ls -t $dir/post_db_copy_* | head -1") $local_archive.gz
else
  echo "Please provide valid argument to download or reuse db."
  exit 0
fi

if [ ! -f $local_archive.gz ]
then
  echo "Unable to download, check logs above."
  exit 0
fi

# Delete un-zipped version if exists.
rm $local_archive

# Un-zip.
gunzip --keep $local_archive.gz

# Drop the current database.
echo "Dropping local database"
drush $alias $l_argument sql-drop --y

# Clear memcache to avoid cache issues during update.
echo "Clearing memcache"
drush $alias $l_argument ssh "sudo service memcached restart"

# Install the dump.
echo "Installing database from $arg2 env"
drush $alias $l_argument sql-cli < $local_archive

# Delete un-zipped version to save space.
rm $local_archive

# Update super admin email to local default.
echo "Update super admin email to no-reply@acquia.com."
drush $alias $l_argument sqlq "update users_field_data set mail = 'no-reply@acquia.com', name = 'admin' where uid = 1"

# Change user 1 password.
echo "Resetting super admin password to admin"
drush $alias $l_argument user-password admin --password="admin"

# Unblock user 1
echo "Unblocking super admin user"
drush $alias $l_argument uublk --name=admin

drush $alias $l_argument en dblog -y

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
  sh "$path/install-site-dev.sh" "$alias" "$l_argument" "$arg2"
fi
