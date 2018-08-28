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

cd `drush8 sa @$site.$target_env | grep root | cut -d"'" -f4`

nothingstr="no update performed"

## Checking if there any install files has been updated.
echo "Checking git diff to identify hook_update() change."
echo $(cat ../git-diff.txt)

## In case install file have been updated.
if echo $(cat ../git-diff.txt) | grep ".install"; then
  ## Restore database dumps before applying database updates.
  echo "Change in install file detected, restoring database before executing updb."
  drush8 acsf-tools-restore --source-folder=~/backup/$target_env/post-stage --gzip --no-prompt=yes

  ## Temporary "manual steps" that need to be performed when upgrading to Drupal 8.5.
  ## This can be removed when Drupal 8.5 will be released live and updated db with Drupal 8.5 will be staged to this environment.
  drush acsf-tools-ml cr
  drush acsf-tools-ml sqlq "DELETE FROM key_value WHERE collection='system.schema' AND name='lightning_scheduled_updates';"

  ## Apply the database updates to all sites.
  echo "Executing updb."
  drush8 acsf-tools-ml updb 2> /tmp/temp
  output=$(cat /tmp/temp | perl -pe 's/\\/\\\\/g' | sed 's/"//g' | sed "s/'//g")
  rm /tmp/temp
  echo $output

  ## Temporary "manual steps" (part 2) that need to be performed when upgrading to Drupal 8.5.
  ## This can be removed when Drupal 8.5 will be released live and updated db with Drupal 8.5 will be staged to this environment.
  drush acsf-tools-ml entity-updates
else
  ## Clear cache for frontend change.
  echo "No change in install files, clearing caches only."
  drush8 acsf-tools-ml cr

  ## Set the output variable which is used to update the Slack channel.
  output=$nothingstr
fi

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

## Push the updb logs on Slack channel.
FILE=$HOME/slack_settings

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  # Post updb done notice to Slack channel.
  errorstr="error"

  if [ -n "$output" ]; then
    if echo $output | grep -q "$errorstr"; then
      echo "Sending error notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Error while executing updb on $target_env. \n$output.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
    elif echo $output | grep -q "$nothingstr"; then
      echo "Sending success notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully cleared cache on $target_env. No database update needed.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
    else
      echo "Sending success notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully executed database restore and update on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
    fi
  else
    echo "No output variable to check."
  fi
else
  echo "File $FILE does not exist."
fi