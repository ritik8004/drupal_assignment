#!/bin/bash
#
# Cloud Hook: post-code-update
#
# The post-code-update hook runs in response to code commits. When you
# push commits to a Git branch, the post-code-update hooks runs for
# each environment that is currently running that branch.
#
# The arguments for post-code-update are the same as for post-code-deploy,
# with the source-branch and deployed-tag arguments both set to the name of
# the environment receiving the new code.
#
# post-code-update only runs if your site is using a Git repository. It does
# not support SVN.

site="$1"
target_env="$2"

slack=0

FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings
  slack=1
else
  echo "$HOME/slack_settings does not exist. Slack won't be notified."
fi

cd `drush8 sa @$site.$target_env | grep root | cut -d"'" -f4`

nothingstr="no update performed"
errorstr="error"

## Checking if there any install files has been updated.
echo "Checking git diff to identify hook_update() change."
echo $(cat ../git-diff.txt)
echo -e "\n"

## In case install file have been updated.
if echo $(cat ../git-diff.txt) | grep "\.install\|docroot/.*/config"; then
  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" We are about to restore database and run updb on $target_env. Sites won't be available during some minutes. This channel will be updated once the process is done.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi

  ## Restore database dumps before applying database updates.
  echo "Change in install file detected, restoring database before executing updb."
  drush8 acsf-tools-restore --source-folder=~/backup/$target_env/post-stage --gzip --no-prompt

  ## Temporary fix of current locale configuration settings until CORE-5300 goes live and be restaged
  ## This can be removed afterwards
  drush acsf-tools-ml cset locale.settings translation.use_source local

  ## Temporary "manual steps" that need to be performed when upgrading to Drupal 8.5.
  ## This can be removed when Drupal 8.5 will be released live and updated db with Drupal 8.5 will be staged to this environment.
  drush acsf-tools-ml cr
  drush acsf-tools-ml sqlq "DELETE FROM key_value WHERE collection='system.schema' AND name='lightning_scheduled_updates';"

  ## Apply the database updates to all sites.
  echo "Executing updb."
  drush8 acsf-tools-ml updb 2> /tmp/drush_updb_$target_env.log
  output=$(cat /tmp/drush_updb_$target_env.log | perl -pe 's/\\/\\\\/g' | sed 's/"//g' | sed "s/'//g")
  echo $output

  ## Temporary "manual steps" (part 2) that need to be performed when upgrading to Drupal 8.5.
  ## This can be removed when Drupal 8.5 will be released live and updated db with Drupal 8.5 will be staged to this environment.
  drush acsf-tools-ml entity-updates

  # Temporary "manual steps". Enable the mobile-app module after restore.
  drush8 acsf-tools-ml en alshaya_mobile_app -y
else
  ## Clear cache for frontend change.
  echo "No change in install files, clearing caches only."
  drush8 acsf-tools-ml cr

  ## Set the output variable which is used to update the Slack channel.
  output=$nothingstr
fi

echo -e "\n"

## Clear varnish caches for all sites of the factory.
domains=$(drush8 acsf-tools-list --fields=domains | grep " " | cut -d' ' -f6 | awk NF)

echo "$domains" | while IFS= read -r line
do
 echo "Clearing varnish cache for $line"
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1495.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1496.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2295.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2296.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
done

echo -e "\n"

if [ $slack == 1 ]; then
  if [ -n "$output" ]; then
    if echo $output | grep -q "$errorstr"; then
      echo "Sending error notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Error while executing updb on $target_env. \n$output.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
    elif echo $output | grep -q "$nothingstr"; then
      echo "Sending success notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully cleared cache on $target_env. No database update needed.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
    else
      echo "Sending success notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully executed database restore and update on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
    fi
  else
    echo "No output variable to check."
  fi
fi
