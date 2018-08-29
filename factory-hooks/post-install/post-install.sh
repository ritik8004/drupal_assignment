#!/bin/bash

target_env="$1"
site="$2"
brand_code="$3"
country_code="$4"

// @TODO: To be removed.
brand_code="mc"

cd `drush8 sa @alshaya.$target_env | grep root | cut -d"'" -f4`

## Push the post-install logs on Slack channel.
FILE=$HOME/slack_settings

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  echo "Sending post-install start notification to Slack channel."
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"txt\": \" New site $brand_code $country_code has been installed on $target_env. Now running post-install script.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
else
  echo "File $HOME/slack_settings does not exist."
fi

# @TODO: Get the logs from setup-fresh-site.sh and send these to Slack.
./.../scripts/setup/setup-fresh-site.sh "$target_env" "$site" "$brand_code" "$country_code" >> $HOME/debug.txt