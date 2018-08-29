#!/bin/bash

target_env="$1"
site="$2"
brand_code="$3"
country_code="$4"

cd `drush8 sa @alshaya.$target_env | grep root | cut -d"'" -f4`

## Push the updb logs on Slack channel.
FILE=/home/alshaya/slack_settings

echo "this is in the bash" >> /home/alshaya/debug.txt
echo $HOME >> /home/alshaya/debug.txt

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . /home/alshaya/slack_settings

  echo "Sending error notification to Slack channel."
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"txt\": \" New site $brand_code $country_code installed on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" /home/alshaya/slack_settings
else
  echo "File /home/alshaya/slack_settings does not exist."
fi

# @TODO: Get the logs from setup-fresh-site.sh and send these to Slack.
./.../scripts/setup/setup-fresh-site.sh "$target_env" "$site" "$brand_code" "$country_code"