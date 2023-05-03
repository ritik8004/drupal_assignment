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

cd "/var/www/html/$subscription.$target_env/docroot"
slack_file="/var/www/html/$subscription.$target_env/scripts/deployment/post_to_slack.sh"

echo -e "\n"

if echo $(cat ../git-diff.txt) | grep "\.scss\|\.js\|\.twig\|\.theme"; then
  echo "Change in FE detected, clearing cache."
  sh $slack_file "Doing drush crf to reflect FE changes on $subscription.$target_env."
  drush acsf-tools-ml crf
fi

# Fix the update version for all the modules.
drush acsf-tools-ml fix-update-version

echo -e "\n"
