
# "download" or "reuse".
arg1="$1"

alias="@alshaya.local"
l_argument=""

remote_alias="@alshaya.01test"
l_argument=" -l whitelabel14.test-alshaya.acsitefactory.com"

# Check if we have an existing archive if we want to reuse existing one.
if [ $arg1 = "reuse" ]
then
  if [ ! -f "/tmp/alshaya_test.sql" ]
  then
    echo "No existing archive found"
    exit 0
  fi
  echo "Re-using downloaded database."
elif [ $arg1 = "download" ]
then
  echo "Downloading latest database from test"
  drush $remote_alias $l_argument sql-dump > /tmp/alshaya_test.sql
else
  echo "Please provide valid argument to download or reuse db."
  exit 0
fi

# Drop the current database.
echo "Dropping local database"
drush $alias sql-drop --y

# Clear memcache to avoid cache issues during update.
echo "Clearing memcache"
drush $alias ssh "sudo service memcached restart"

# Install the dump.
echo "Installing database from test env"
drush $alias sql-cli < /tmp/alshaya_test.sql

# Update configs for oauth
echo "Updating config for oauth."
drush $alias cset simple_oauth.settings private_key "/var/www/alshaya/box/alshaya_acm" -y
drush $alias cset simple_oauth.settings public_key "/var/www/alshaya/box/alshaya_acm.pub" -y

# Update configs for autologout
echo "Updating config for autologout."
drush $alias cset autologout.settings timeout 86400 -y
drush $alias cset autologout.settings max_timeout 86400 -y

# Update super admin email to local default.
echo "Update super admin email to no-reply@acquia.com."
drush $alias sqlq "update users_field_data set mail = 'no-reply@acquia.com' where uid = 1"

# Change user 1 password.
echo "Resetting super admin password to admin"
drush $alias user-password admin --password="admin"

# Unblock user 1
echo "Unblocking super admin user"
drush $alias uublk --name=admin

# Clear the cache.
echo "Clear cache"
drush $alias cr

# Execute dev steps.
path=$(dirname $0)
if [ -f "$path/install-site-dev.sh" ]
then
  echo "Execute dev steps."
  sh "$path/install-site-dev.sh" "$alias"
fi
