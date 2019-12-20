#!/bin/bash
#
# ./reset-form-post-stage-dumps.sh "alshaya" "01dev"
#

subscription="$1"
target_env="$2"

# Get the environment without the "01" prefix.
env=${target_env:2}

# Try to load the Slack webhook URL stored on the server.
slack=0
FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  . $HOME/slack_settings
  slack=1
else
  echo "$HOME/slack_settings does not exist. Slack won't be notified."
fi

cd `drush sa @$subscription.$target_env --fields=root | grep root | cut -d" " -f4`

errorstr="error"

if [ $slack == 1 ]; then
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" We are about to restore databases and run updb on $target_env. Sites won't be available during some minutes. This channel will be updated once the process is done.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
fi

## Browse the sites one by one.
while IFS= read -r site
do
  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Restoring database and run updb on $target_env.$site.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi

  ## Restore database dump before applying database updates.
  if [ ! -f ~/backup/$target_env/post-stage/$site.sql.gz ]; then
    echo "Could not find a dump to restore for $target_env.$site."
    if [ $slack == 1 ]; then
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Could not find a dump to restore for $target_env.$site.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
    fi
  else
    gunzip -k ~/backup/$target_env/post-stage/$site.sql.gz
    drush -l $site.$env-alshaya.acsitefactory.com sql-drop -y
    `drush -l $site.$env-alshaya.acsitefactory.com sql-connect` < ~/backup/$target_env/post-stage/$site.sql
    rm ~/backup/$target_env/post-stage/$site.sql

    # Clean commerce data to refresh ones from dump.
    drush -l $site.$env-alshaya.acsitefactory.com sync-commerce-cats -y
    drush -l $site.$env-alshaya.acsitefactory.com sync-options
    drush -l $site.$env-alshaya.acsitefactory.com sync-stores
    drush -l $site.$env-alshaya.acsitefactory.com sync-commerce-promotions
    drush -l $site.$env-alshaya.acsitefactory.com queue-run acq_promotion_attach_queue
    drush -l $site.$env-alshaya.acsitefactory.com queue-run acq_promotion_detach_queue
    drush -l $site.$env-alshaya.acsitefactory.com sapi-i

    ## Clearing cache before running updb to the site.
    drush -l $site.$env-alshaya.acsitefactory.com cr
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
        curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Error while executing updb on $target_env.$site. \n$output.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
      else
        echo "Sending success notification to Slack channel."
        curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Successfully executed database restore and updb on $target_env.$site.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
      fi
    else
      echo "No output variable to check."
    fi
  fi

done <<< "$(drush acsf-tools-list --fields)"
