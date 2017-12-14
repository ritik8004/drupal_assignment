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

drush acsf-tools-ml updb

var = drush acsf-tools-list --fields=domains | grep " " | cut -d' ' -f6 | awk NF

echo "$var" | while IFS= read -r line
do
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1495.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-1496.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2295.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
 curl -X BAN -H "X-Acquia-Purge:alshaya" https://bal-2296.enterprise-g1.hosting.acquia.com/* -H "Host: $line" -k
done