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

site="$1"
target_env="$2"

cd `drush8 sa @$site.$target_env | grep root | cut -d"'" -f4`

echo "Executing updb."

output="$( bash <<EOF
drush8 acsf-tools-ml updb
EOF
)"

##Pushing the updb logs on slack channel
SLACK_WEBHOOK_URL="https://hooks.slack.com/services/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"

if [ -n "$output" ]; then
        curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \" Executing updb on $target_env. \n$output.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
fi

domains=$(drush8 acsf-tools-list --fields=domains | grep " " | cut -d' ' -f6 | awk NF)

echo "$domains" | while IFS= read -r line
do
 echo "Clearing varnish cache for $line"
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1495.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1496.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2295.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2296.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
done