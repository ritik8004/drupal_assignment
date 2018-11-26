#!/bin/bash
#
# Cloud Hook: post-staging-update
#

SITEGROUP="$1"
ENVIRONMENT="$2"
DB_ROLE="$3"
DOMAIN="$4"

FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" $DOMAIN site have been staged on $ENVIRONMENT and is now waiting for post-stage operations.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
else
  echo "$HOME/slack_settings does not exist. Slack won't be notified."
fi

