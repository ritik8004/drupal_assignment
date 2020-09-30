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
  exit
fi

# Validate mode is supported.
if [ "$mode" != "prep" -a "$mode" != "updb" -a "$mode" != "hotfix" -a "$mode" != "hotfix_crf" ]
then
  echo "Deployment mode $mode not supported."
  echo "Command usage: deploy_tag.sh BRANCH TAG MODE"
  echo "Example for preparation mode: deploy_tag.sh main 5.6.0-build prep"
  echo "Example for updb mode: deploy_tag.sh main 5.6.0-build updb"
  echo "Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix"
  echo "Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf"
  exit
fi

stack=`whoami`
repo="$stack@svn-25.enterprise-g1.hosting.acquia.com:$stack.git"

server_root="/var/www/html/$AH_SITE_NAME"
docroot="${server_root}/docroot"
log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-deployments.log

if [[ "$AH_SITE_ENVIRONMENT" == *"live"* ]]
then
  base_uri = ".factory.alshaya.com"
else
  env_suffix=`echo $AH_SITE_ENVIRONMENT | sed -e "s/[0-9]*^*//"`
  base_uri="-${env_suffix}.factory.alshaya.com"
fi

log_message()
{
  message=$1
  echo "$message. Date `date`, Tag $tag, Stack $stack" | tee -a ${log_file}
  echo
}

log_message "Deployment Branch: $branch"
log_message "Repo: $repo"
log_message "Docroot: $docroot"
log_message "Log file: $log_file"
log_message "Base URI: $base_uri"

log_message "Starting deployment in mode $mode"

backup_directory="${HOME}/${AH_SITE_ENVIRONMENT}/backup/pre-$tag"
directory="${HOME}/${AH_SITE_ENVIRONMENT}/repo"

# Create folder and clone if not available
if [ ! -d "$directory/$stack" ]
then
  log_message "Repo directory $directory not available, creating and cloning"

  mkdir -p $directory
  cd $directory
  git clone $repo &>> ${log_file}

  if [ $? -ne 0 ]
  then
    log_message "Failed to clone repo, aborting"
    exit
  else
    log_message "Repo cloned successfully"
  fi
fi

if [ ! -d "$directory/$stack" ]
then
  log_message "Repo directory not available still, aborting"
  exit
fi

cd "$directory/$stack"

# Fetch all tags.
log_message "Fetching tags"
git fetch origin --tags &>> ${log_file}

# Validate if tag exists.
if [ ! $(git tag -l "$tag") ]; then
  log_message "Error: Tag not found, aborting"
  exit
fi

# Checkout deployment branch used for deployment.
log_message "Checkout $branch"
git checkout $branch &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message "Failed to checkout branch $branch, aborting"
  exit
fi

# Reset the code to match the tag.
log_message "Reset to $tag"
git reset --hard $tag &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message "Failed to reset to tag, aborting"
  exit
fi

if [ "$mode" = "prep" ]
then
  log_message "Release preparation completed"
  exit
fi

# Taking backup now.
log_message "Take DB backup"
mkdir -p "$backup_directory"
drush --root=$docroot acsf-tools-dump --result-folder=$backup_directory -y -v --gzip &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message "Failed to take backup, aborting"
  exit
fi

# Enable maintenance mode if mode is updb.
if [ "$mode" = "updb" ]
then
  log_message "Turning maintenance on"
  drush --root=$docroot sfmlc alshaya-enable-maintenance &>> ${log_file}
  if [ $? -ne 0 ]
  then
    log_message "Failed to enable maintenance mode, aborting"
    exit
  fi
fi

# Force the push to avoid issues with previous commit history.
log_message "Pushing changes"
git push origin $branch --force &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message "Failed to deploy code, aborting"
  exit
fi

# Wait for code to be available on server before moving forward.
deployment_identifier=$(cat "$server_root/deployment_identifier")
while [ "${deployment_identifier}" != "${tag}" ]
do
  log_message "Waiting for code to be deployed on server (current=$deployment_identifier)"
  sleep 5
  deployment_identifier=$(cat "$server_root/deployment_identifier")
done

log_message "Code deployment finished"

if [ "$mode" = "updb" ]
then
  log_message "Running updates"
  for site in `drush --root=$docroot acsf-tools-list | grep -v " "`
  do
    log_message "Running updates on $site"
    drush --root=$docroot -l "${site}${base_uri}" updb -y &>> ${log_file}
    if [ $? -ne 0 ]
    then
      log_message "$site: UPDB FAILED, site kept offline still, please check logs"
    else
      drush --root=$docroot -l "${site}${base_uri}" alshaya-disable-maintenance &>> ${log_file}
      log_message "$site: UPDB done and site put back online"

      drush --root=$docroot -l "${site}${base_uri}" cr -y &>> ${log_file}
      log_message "$site: CR done"
    fi
  done

  drush --root=$docroot cc drush -y &>> ${log_file}
fi

if [ "$mode" = "hotfix_crf" ]
then
  log_message "Doing CRF now as requested"
  drush --root=$docroot sfml crf --delay=10 &>> ${log_file}
fi
