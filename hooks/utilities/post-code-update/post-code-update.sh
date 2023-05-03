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

cd `drush9 sa @$subscription.$target_env | grep "^  root" | cut -d" " -f4`

## Checking if any install/config file have been updated.
echo "Checking git diff to identify hook_update() or config change."
echo $(cat ../git-diff.txt)
echo -e "\n"

## In case install/config file have been updated, we reset the sites.
if echo $(cat ../git-diff.txt) | grep "\.install\|docroot/.*/config"; then
  # @TODO: Test this and enable it back after we manage to stage sites more frequently.
  # echo "Change in install file detected, restoring databases before executing updb."
  # ./../scripts/utilities/reset-from-post-stage-dumps.sh $subscription $target_env

  echo "Change in install file detected, executing updb."

  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"${AH_SITE_NAME}\", \"text\": \"Executing drupal updates on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi
  drush acsf-tools-ml updb
elif echo $(cat ../git-diff.txt) | grep "\.scss\|\.js\|\.twig\|\.theme"; then
  echo "Change in FE detected, clearing cache."

  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"${AH_SITE_NAME}\", \"text\": \"Clearing drupal cache to reflect FE changes on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi
  drush acsf-tools-ml crf
else
  if [ $slack == 1 ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"${AH_SITE_NAME}\", \"text\": \"No database update needed on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  fi
fi

# Fix the update version for all the modules.
drush acsf-tools-ml fix-update-version

echo -e "\n"

# Commenting this as we do not do it on production.
# @TODO: Remove after we validate no negative impact on testing for at-least a month.
## Clear varnish caches for all domains of this environment.
# ./../scripts/utilities/clear-varnish.sh $subscription $target_env

if [ $slack == 1 ]; then
  # curl -X POST --data-urlencode "payload={\"username\": \"${AH_SITE_NAME}\", \"text\": \"Varnish cache cleared on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
  curl -X POST --data-urlencode "payload={\"username\": \"${AH_SITE_NAME}\", \"text\": \"Post code update on $target_env finished.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
fi
