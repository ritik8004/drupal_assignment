#!/bin/bash

site="alshaya"
target_env="$1"
brand_code="$2"
country_code="$3"

cd `drush8 sa @$site.$target_env | grep root | cut -d"'" -f4`

## Push the updb logs on Slack channel.
FILE=$HOME/slack_settings

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  echo "Sending error notification to Slack channel."
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"txt\": \" New site $brand_code $country_code installed on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
else
  echo "File $FILE does not exist."
fi