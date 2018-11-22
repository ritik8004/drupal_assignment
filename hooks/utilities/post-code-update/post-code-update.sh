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

target_env="$2"

# Get the environment without the "01" prefix.
env=${target_env:2}

slack=0

FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings
  slack=1
else
  echo "$HOME/slack_settings does not exist. Slack won't be notified."
fi

cd `drush sa @alshaya.$target_env | grep root | cut -d"'" -f4`

errorstr="error"

## Checking if there any install files has been updated.
echo "Checking git diff to identify hook_update() change."
echo $(cat ../git-diff.txt)
echo -e "\n"

## In case install file have been updated.
if echo $(cat ../git-diff.txt) | grep "\.install\|docroot/.*/config"; then
  ## Notify Slack about ongoing update.
  echo "Change in install file detected, restoring databases before executing updb."
  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" We are about to restore databases and run updb on $target_env. Sites won't be available during some minutes. This channel will be updated once the process is done.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi

  ## Browse the sites one by one.
  while IFS= read -r site
  do
    if [ $slack == 1 ]; then
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Restoring database and run updb on $site.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
    fi

    ## Restore database dump before applying database updates.
    if [ ! -f ~/backup/$target_env/post-stage/$site.sql.gz ]; then
      echo "Could not find a dump to restore for $site."
      if [ $slack == 1 ]; then
        curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Could not find a dump to restore for $site.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
      fi
    else
      gunzip -k ~/backup/$target_env/post-stage/$site.sql.gz
      drush -l $site.$env-alshaya.acsitefactory.com sql-drop -y
      drush -l $site.$env-alshaya.acsitefactory.com sql-connect < ~/backup/$target_env/post-stage/$site.sql
      rm ~/backup/$target_env/post-stage/$site.sql
    fi

    ## Apply the database updates to the site.
    echo "Executing updb."
    drush -l $site.$env-alshaya.acsitefactory.com updb 2> /tmp/drush_updb_$site_$target_env.log
    output=$(cat /tmp/drush_updb_$target_env.log | perl -pe 's/\\/\\\\/g' | sed 's/"//g' | sed "s/'//g")
    echo $output

    if [ $slack == 1 ]; then
      if [ -n "$output" ]; then
        if echo $output | grep -q "$errorstr"; then
          echo "Sending error notification to Slack channel for $site."
          curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Error while executing updb on $site. \n$output.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
        else
          echo "Sending success notification to Slack channel."
          curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully executed database restore and updb on $site.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
        fi
      else
        echo "No output variable to check."
      fi
    fi

  done <<< "$(drush acsf-tools-list --fields)"


else
  ## Clear cache for frontend change.
  echo "No change in install files, clearing caches only."
  drush acsf-tools-ml cr

  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Successfully cleared cache on $target_env. No database update needed.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi
fi

echo -e "\n"

## Clear varnish caches for all sites of the factory.
domains=$(drush acsf-tools-list --fields=domains | grep " " | cut -d' ' -f6 | awk NF)

echo "$domains" | while IFS= read -r line
do
 echo "Clearing varnish cache for $line"
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1495.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1496.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2295.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2296.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k -s > /dev/null
done

if [ $slack == 1 ]; then
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Varnish caches cleared on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Post code update on $target_env finished.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
fi