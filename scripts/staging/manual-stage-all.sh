#!/bin/bash
#
# This script manually stages given sites from production to given environment.
#
# ./manual-stage-all.sh target mode
# ./manual-stage-all.sh uat reset
# ./manual-stage-all.sh pprod iso

log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-staging.log

echo $log_file
echo

target="$1"
mode="$2"
user=`whoami`
target_env=`echo $AH_SITE_ENVIRONMENT | sed -r "s/live/${target}/g"`

for site in `drush "@${user}.${target_env}" ssh "drush acsf-tools-list" | grep -v " "`
do
  echo "Started staging for ${site} ${target_env} ${mode} `date`" &>> ${log_file}
  ~/manual-stage.sh ${site} $target_env $mode &>> ${log_file}
  echo "Finished staging for ${site} ${target_env} ${mode} `date`" &>> ${log_file}
  echo "" &>> ${log_file}
done
