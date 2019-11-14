#!/bin/bash
#
# FLOCK script for the cron.
#
# First argument(id) can be any name and this will be used for flock.
# Second argument(command) will be the actual drush command that will be run.
#
# Usage -
# 1. Without any option - ./scripts/cron/cron_flock.sh acspm acspm
# 2. With options -  ./scripts/cron/cron_flock.sh acspm '"acspm" "--types=cart"'
#

id="$1"
command="$2"

file_name=/tmp/cron-lock-$id.lock
log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-${id}.log

# Current timestamp
current=`date +%s`

# 24hr seconds
seconds_check=86400

# Flag to determine whether cron will process or not.
process_cron=true

# Check if there is currently a lock in place.
if [ -f $file_name ]
then
    # Checking if lock file is old than the given time.
    if [ $(($current - `stat -c "%Y" $file_name`)) -lt $seconds_check ]
    then
        process_cron=false
    fi
fi

if [ "$process_cron" = true ]; then
    echo "Creating lock file ${file_name}" &>> ${log_file}
    touch $file_name
    cd /var/www/html/${AH_SITE_NAME}/docroot
    drush acsf-tools-ml ${command} &>> ${log_file}
    #  Releasing the lock.
    rm /tmp/cron-lock-$id.lock
    echo "Lock ${log_file} is now released." &>> ${log_file}
else
    echo "Skipping as command:$command with id:$id already running." &>> ${log_file}
fi
