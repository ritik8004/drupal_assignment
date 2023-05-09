#!/bin/bash

message="$1"

FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  . $HOME/slack_settings
  curl -X POST --data-urlencode "payload={\"username\": \"${AH_SITE_NAME}\", \"text\": \"<!here> ${message} - ${AH_SITE_NAME}\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
fi
