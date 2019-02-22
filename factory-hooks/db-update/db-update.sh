#!/bin/bash
#
# Factory Hook: db-update
#
# The existence of one or more executable files in the
# /factory-hooks/db-update directory will prompt them to be run *instead of* the
# regular database update (drush updatedb) command. So that update command will
# normally be part of the commands executed below.
#
# Usage: post-code-deploy site env db-role domain custom-arg
# Map the script inputs to convenient names.
# Acquia hosting site / environment names
site="$1"
env="$2"
# database role. (Not expected to be needed in most hook scripts.)
db_role="$3"
# The public domain name of the website.
domain="$4"

# Load the Slack webhook URL (which is not stored in this repo).
. $HOME/slack_settings

# Send slack notification when db-update cloud hook was successfully invoked.
curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Invoked (currently empty) db-update factory hook for environment *$site.$env* and domain *$domain*.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
