#!/bin/sh

# Usage: scripts/download-db.sh "site" "env"
# Purpose of script is to only download the database

# "mckw" or "hmkw".
site="$1"

# "01dev" or "01test" or "01uat" or "01live".
env="$2"

remote_alias="@alshaya.$env"

# Get acsf info from remote.
remote_info=$(drush $remote_alias acsf-tools-list --fields=name,domains)
remote_url=$(echo "$remote_info" | php scripts/get-acsf-info.php $site url)
remote_l_argument="-l $remote_url"
echo "Remote URL: $remote_url"

# Local archive path to download.
local_archive="../tmp/alshaya_${site}_${env}.sql"
echo "Local archive: $local_archive"

# Create the directory every-time, not heavy call.
mkdir -p "../tmp"

echo "Downloading latest database from $env"
drush $remote_alias $remote_l_argument sql-dump > $local_archive
echo "Download complete"

exit 0
