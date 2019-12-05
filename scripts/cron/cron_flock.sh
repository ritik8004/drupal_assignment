#!/bin/bash
#
# FLOCK script for the cron.
#
# First argument(id) can be any name and this will be used for flock.
# Second argument(command) will be the actual drush command that will be run.
#
# If command argument contains the `.sh`, means we running a script otherwise
# it assume that it will be drush command.
#
# Usage -
# 1. Without any option (for drush) - ./scripts/cron/cron_flock.sh acspm acsf-tools-ml acspm
# 2. With options (for drush) -  ./scripts/cron/cron_flock.sh acspm acsf-tools-ml '"acspm" "--types=cart"'
# 3. Without option (for scripts) - ./scripts/cron/cron_flock.sh clear-varnish /var/www/fullpath/clear-varnish.sh
# 4. With option (for scripts) - ./scripts/cron/cron_flock.sh clear-varnish `/var/www/fullpath/clear-varnish.sh arg1 arg2`
#
id=$1
args=("$@")

command=$2
for i in $(seq 2 ${#args[@]})
do
  command="$command \"${args[$i]}\""
done

file_name=/tmp/cron-lock-$id.lock
log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-${id}.log

# Current timestamp
current=`date +%s`

# 24hr seconds
seconds_check=86400

# Flag to determine whether cron will process or not.
process_cron=true

# Checking if argument 2 (command) passed is a shell script or not.
# If argument2/command contains '.sh', it means it is a shell script
# or this is a drush command.
is_shell_script=false
if [[ ${command} == *".sh"* ]]
then
    is_shell_script=true
fi

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
    echo "Creating lock file ${file_name}"
    echo "Creating lock file ${file_name}" &>> ${log_file}
    touch $file_name

    # Add start date in logs to analyse.
    echo "Starting at `date`"

    # If a script is given, then run it. Else run drush command.
    if [ "$is_shell_script" = true ]; then
        echo "Running script ${command}"
        bash $command &>> ${log_file}
    else
        command="drush $command"
        echo "Running drush $command"
        cd /var/www/html/${AH_SITE_NAME}/docroot
        eval $command &>> ${log_file}
    fi

    # Add finish date in logs to analyse.
    echo "Ended at `date`"

    #  Releasing the lock.
    rm /tmp/cron-lock-$id.lock
    echo "Lock ${file_name} is now released."
    echo "Lock ${file_name} is now released." &>> ${log_file}
else
    echo "Skipping as command:$command with id:$id already running."
    echo "Skipping as command:$command with id:$id already running." &>> ${log_file}
fi
