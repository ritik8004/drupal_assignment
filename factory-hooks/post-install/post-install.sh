#!/bin/bash

site="alshaya"
target_env="$1"
brand_code="$2"
country_code="$3"

cd `drush8 sa @$site.$target_env | grep root | cut -d"'" -f4`

## Push the updb logs on Slack channel.
FILE=/home/alshaya/slack_settings

echo "this is in the bash" >> /home/alshaya/debug.txt

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . /home/alshaya/slack_settings

  echo "Sending error notification to Slack channel."
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"txt\": \" New site $brand_code $country_code installed on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" /home/alshaya/slack_settings
else
  echo "File /home/alshaya/slack_settings does not exist."
fi