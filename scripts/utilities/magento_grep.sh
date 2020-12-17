#!/bin/bash
#
# This script does rsync for files of a site between stacks.
#
# ./scripts/utilities/grep-magento-logs.sh "search string" "server count" "server url"
# ./scripts/utilities/magento_grep.sh "5fdb35579ab1f" 6 ent-tw5uijob6hir2-staging-5em2ouy@ssh.eu-3.magento.cloud

search="$1"
count="$2"
server_url="$3"

for ((i=1;i<=$count;i++));
do
  echo "Searching on $i"
  ssh $i.$server_url "grep $search var/log/*.log";
  echo
done
