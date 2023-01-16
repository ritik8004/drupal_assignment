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

server_root="/var/www/html/$AH_SITE_NAME"
slack_file="${server_root}/scripts/deployment/post_to_slack.sh"

target="$1"
mode="$2"
user=`whoami`
target_env=`echo $AH_SITE_ENVIRONMENT | sed -r "s/live/${target}/g"`

sh $slack_file "Manual staging started for environment ${target_env} for all the sites in mode ${mode}"

for site in `drush "@${user}.${target_env}" ssh "drush acsf-tools-list" | grep -v " "`
do
  echo "Started staging for ${site} ${target_env} ${mode} `date`" &>> ${log_file}
  ~/manual-stage.sh ${site} $target_env $mode &>> ${log_file}
  echo "Finished staging for ${site} ${target_env} ${mode} `date`" &>> ${log_file}
  echo "" &>> ${log_file}

  if [[ "$mode" == "reset" ]]; then
    echo "" &>> ${log_file}
    echo "Sleeping for 30 minutes before starting for next site" &>> ${log_file}
    sleep 1800
    echo "" &>> ${log_file}
  fi
done

sh $slack_file "Manual staging finished for environment ${target_env} for all the sites in mode ${mode}"

if [[ "$mode" == "iso" ]]; then
  sh $slack_file "Please run \`drush updb\` now on ${user}.${target_env}."
fi
