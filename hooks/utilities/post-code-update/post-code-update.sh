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

## Restore the database dumps before applying database updates.
echo "Restore database dumps."
drush8 acsf-tools-restore --source-folder=~/backup/post-stage --gzip

## Apply the database updates.
echo "Executing updb."
drush8 acsf-tools-ml updb 2> /tmp/temp
output=$(cat /tmp/temp | perl -pe 's/\\/\\\\/g')
rm /tmp/temp
echo $output

## Clear caches as it is not done if there is database updates but may still
## be required for some frontend changes.
## We may want to do it only on sites without updates.
echo "Clearing caches."
drush8 acsf-tools-ml cr

## Clear varnish caches for all sites of the factory.
domains=$(drush8 acsf-tools-list --fields=domains | grep " " | cut -d' ' -f6 | awk NF)

echo "$domains" | while IFS= read -r line
do
 echo "Clearing varnish cache for $line"
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1495.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1496.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2295.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2296.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
done

## Push the updb logs on Slack channel.
FILE=$HOME/slack_settings

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  # Post updb done notice to Slack channel.
  errorstr="error"

  if [ -n "$output" ]; then
    if echo $output | grep -q $errorstr; then
      echo "Sending error notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Error while executing updb on $target_env. \n$output.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
    else
      echo "Sending success notification to Slack channel."
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully executed updb on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
    fi
  else
    echo "No output variable to check."
  fi
else
  echo "File $FILE does not exist."
fi