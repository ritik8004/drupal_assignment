#!/bin/bash

# This script must be executed from inside production server.
# Command usage: deploy_tag.sh TAG MODE
# Example for preparation mode: deploy_tag.sh main 5.6.0-build prep
# Example for updb mode: deploy_tag.sh branch 5.6.0-build updb
# Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix
# Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf
# Example for hotfix mode and do CR at the end: deploy_tag.sh main 5.6.1-build hotfix_cr

branch="$1"
tag="$2"
mode="$3"

if [ -z "$branch" -o -z "$tag" -o -z "$mode" ]
then
  echo "Deployment branch, Tag to deploy and deployment mode are required."
  echo "Command usage: deploy_tag.sh BRANCH TAG MODE"
  echo "Example for preparation mode: deploy_tag.sh main 5.6.0-build prep"
  echo "Example for updb mode: deploy_tag.sh main 5.6.0-build updb"
  echo "Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix"
  echo "Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf"
  echo "Example for hotfix mode and do CR at the end: deploy_tag.sh main 5.6.1-build hotfix_cr"
  exit
fi

# Validate mode is supported.
if [ "$mode" != "prep" -a "$mode" != "updb" -a "$mode" != "hotfix" -a "$mode" != "hotfix_crf" -a "$mode" != "hotfix_cr" ]
then
  echo "Deployment mode $mode not supported."
  echo "Command usage: deploy_tag.sh BRANCH TAG MODE"
  echo "Example for preparation mode: deploy_tag.sh main 5.6.0-build prep"
  echo "Example for updb mode: deploy_tag.sh main 5.6.0-build updb"
  echo "Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix"
  echo "Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf"
  echo "Example for hotfix mode and do CR at the end: deploy_tag.sh main 5.6.1-build hotfix_cr"
  exit
fi

stack=`whoami`
repo="$stack@svn-5975.enterprise-g1.hosting.acquia.com:$stack.git"

server_root="/var/www/html/$AH_SITE_NAME"
docroot="${server_root}/docroot"
log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-deployments.log
deployment_identifier=$(cat "$server_root/deployment_identifier")
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [[ "$AH_SITE_ENVIRONMENT" == *"live"* ]]
then
  base_uri=".factory.alshaya.com"
else
  env_suffix=`echo $AH_SITE_ENVIRONMENT | sed -e "s/[0-9]*^*//"`
  base_uri="-${env_suffix}.factory.alshaya.com"
fi

log_message()
{
  message=$1
  echo "$message" | tee -a ${log_file}
  echo
}

log_message "============================================"
log_message "Deployment Mode: $mode"
log_message "Tag to deploy: $tag"
log_message "Tag currently deployed: $deployment_identifier"
log_message "Deployment Branch: $branch"
log_message "Repo: $repo"
log_message "Docroot: $docroot"
log_message "Log file: $log_file"
log_message "Base URI: $base_uri"
log_message "Script Directory: $script_dir"

echo
read -p "Please confirm details above and say proceed to start the release: " proceed
echo
if [ "$proceed" = "proceed" ]
then
  screen -dm bash -c "cd $script_dir; ./deploy_tag_final.sh main-DO-NOT-TOUCH $tag $mode";
  echo "Release started, please tail the logs to watch for the updates."
  echo
else
  log_message "Release aborted."
fi
