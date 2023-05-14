#!/bin/bash

# This script must be executed from inside production server.
# Command usage: deploy_tag.sh TAG MODE
# Example for preparation mode: deploy_tag.sh main 5.6.0-build prep
# Example for updb mode: deploy_tag.sh branch 5.6.0-build updb
# Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix
# Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf

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

log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-deployments.log

server_root="/var/www/html/$AH_SITE_NAME"
deployment_identifier=$(cat "$server_root/deployment_identifier")
docroot="${server_root}/docroot"
blt_dir="${server_root}/vendor/acquia/blt"
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
slack_file="${script_dir}/post_to_slack.sh"
clear_caches_post_command="${script_dir}/../cloudflare/clear_caches_post_command.sh"
clear_caches_post_command_site="${script_dir}/../cloudflare/clear_caches_post_command_site.sh"

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

log_message_and_details()
{
  message=$1
  echo "$message. Date `date`, Tag $tag, Stack $stack, Mode $mode" | tee -a ${log_file}
  echo
}

check_code_deployed()
{
  check_code_deployed_response=`$blt_dir/bin/blt cloud-check-code-deployed $tag`
  log_message_and_details "Response for BLT command to check if code deployed on all web servers: $check_code_deployed_response."
  if [ $? -ne 0 ]
  then
    log_message_and_details "Error occurred while checking if code deployed on all the web servers, aborting."
    exit
  fi
}

log_message "============================================"
log_message "Tag to deploy: $tag"
log_message "Tag currently deployed: $deployment_identifier"
log_message "Deployment Branch: $branch"
log_message "Repo: $repo"
log_message "Docroot: $docroot"
log_message "Log file: $log_file"
log_message "Base URI: $base_uri"

log_message_and_details "Starting release in mode $mode."
sh $slack_file "Release started in mode $mode for tag $tag."

backup_directory="${HOME}/${AH_SITE_ENVIRONMENT}/backup/pre-$tag"
directory="${HOME}/${AH_SITE_ENVIRONMENT}/repo"

# Create folder and clone if not available
if [ ! -d "$directory/$stack" ]
then
  log_message_and_details "Repo directory $directory not available, creating and cloning."

  mkdir -p $directory
  cd $directory
  git clone -b $branch $repo &>> ${log_file}

  if [ $? -ne 0 ]
  then
    log_message_and_details "Failed to clone repo, aborting."
    exit
  else
    log_message_and_details "Repo cloned successfully."
  fi
fi

if [ ! -d "$directory/$stack" ]
then
  log_message_and_details "Repo directory not available even after trying to clone, aborting."
  exit
fi

cd "$directory/$stack"

# Fetch all tags.
log_message_and_details "Fetching tags."
git fetch origin --tags &>> ${log_file}

# Validate if tag exists.
if [ ! $(git tag -l "$tag") ]; then
  log_message_and_details "Error: Tag not found, aborting."
  exit
fi

# Checkout deployment branch used for deployment.
log_message_and_details "Checkout $branch."
git checkout $branch &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message_and_details "Failed to checkout branch $branch, aborting."
  exit
fi

# Reset the code to match the tag.
log_message_and_details "Reset to $tag."
git reset --hard $tag &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message_and_details "Failed to reset to tag, aborting."
  exit
fi

if [ "$mode" = "prep" ]
then
  log_message_and_details "Release preparation completed."
  sh $slack_file "Release preparation completed for tag $tag."
  exit
fi

# Create an orphan commit.
log_message_and_details "Resetting $branch git history."
git config user.name "Deployment Script"
git config user.email "noreply@acquia-deployer.com"
git checkout --orphan $branch-tmp &>> ${log_file}
git add . &>> ${log_file}
git commit -m "Orphan commit from $tag." --quiet &>> ${log_file}
git branch -D $branch &>> ${log_file}
git branch -m $branch &>> ${log_file}
git prune
if [ $? -ne 0 ]
then
  log_message_and_details "Failed to reset the branch history, aborting"
  exit
fi

# Taking backup now.
log_message_and_details "Taking DB backup now."
mkdir -p "$backup_directory"
drush --root=$docroot acsf-tools-dump --result-folder=$backup_directory -y -v --gzip &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message_and_details "Failed to take backup, aborting."
  exit
fi

# Enable maintenance mode if mode is updb.
if [ "$mode" = "updb" ]
then
  log_message_and_details "Turning maintenance on all the sites."
  $clear_caches_post_command alshaya-enable-maintenance 0 &>> ${log_file}
  if [ $? -ne 0 ]
  then
    log_message_and_details "Failed to enable maintenance mode, aborting."
    exit
  fi
fi

# Force the push to avoid issues with previous commit history.
log_message_and_details "Pushing changes to the branch."
git push origin $branch --force &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message_and_details "Failed to deploy code, aborting."
  exit
fi

# Wait for code to be available on server before moving forward.
while [ "${deployment_identifier}" != "${tag}" ]
do
  log_message_and_details "Waiting for code to be deployed on server (current=$deployment_identifier)."
  sleep 5
  deployment_identifier=$(cat "$server_root/deployment_identifier")
done

log_message_and_details "Checking on all the webs if deployment is still in process."
check_code_deployed

while [ "${check_code_deployed_response}" != "0" ]
do
  log_message_and_details "Waiting for code to be deployed on all web servers."
  sleep 15
  check_code_deployed
done

log_message_and_details "Code deployment finished."

if [ "$mode" = "updb" ]
then
  log_message_and_details "Running updates on all the sites, one at a time."
  for site in `drush --root=$docroot acsf-tools-list | grep "1: " | tr "1: " " " | tr -d " "`
  do
    log_message_and_details "Running updates on $site"
    drush --root=$docroot -l "${site}" updb -y &>> ${log_file}
    drush --root=$docroot -l "${site}" updb -y &>> ${log_file}
    if [ $? -ne 0 ]
    then
      log_message_and_details "$site: UPDB FAILED, site kept offline still, please check logs."
      sh $slack_file "$site: UPDB FAILED, site kept offline still, please check logs."
    else
      drush --root=$docroot -l "${site}" alshaya-disable-maintenance &>> ${log_file}
      log_message_and_details "$site: UPDB done and site put back online."

      $clear_caches_post_command_site $site cr 5 &>> ${log_file}
      log_message_and_details "$site: CR done."
      echo
      printf "%80s" " " | tr ' ' '='
      echo
      echo
      sh $slack_file "$site: UPDB done and site put back online."
    fi
  done

  drush --root=$docroot cc drush -y &>> ${log_file}
fi

if [ "$mode" = "hotfix_crf" ]
then
  log_message_and_details "Doing CRF now as requested on all the sites."
  $clear_caches_post_command crf 20 &>> ${log_file}
fi

if [ "$mode" = "hotfix_cr" ]
then
  log_message_and_details "Doing CR now as requested on all the sites."
  $clear_caches_post_command cr 30 &>> ${log_file}
fi

log_message_and_details "Release completed."
sh $slack_file "Release completed in mode $mode for tag $tag."
