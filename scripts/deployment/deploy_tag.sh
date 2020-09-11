#!/bin/bash

# This script must be executed from inside production server.
# Command usage: deploy_tag.sh TAG MODE
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
  echo "Example for updb mode: deploy_tag.sh main 5.6.0-build updb"
  echo "Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix"
  echo "Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf"
  exit
fi

# Validate mode is supported.
if [ "$mode" != "updb" -a "$mode" != "hotfix" -a "$mode" != "hotfix_crf" ]
then
  echo "Deployment mode $mode not supported."
  echo "Command usage: deploy_tag.sh BRANCH TAG MODE"
  echo "Example for updb mode: deploy_tag.sh main 5.6.0-build updb"
  echo "Example for hotfix mode: deploy_tag.sh main 5.6.1-build hotfix"
  echo "Example for hotfix mode and do CRF at the end: deploy_tag.sh main 5.6.1-build hotfix_crf"
  exit
fi

stack=`whoami`
repo="$stack@svn-25.enterprise-g1.hosting.acquia.com:$stack.git"

docroot="/var/www/html/$AH_SITE_NAME/docroot"
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
git fetch origin --tags

# Validate if tag exists.
if [ ! $(git tag -l "$tag") ]; then
  log_message "Error: Tag not found, aborting"
  exit
fi

# Checkout deployment branch used for deployment.
git checkout $branch
if [ $? -ne 0 ]
then
  log_message "Failed to checkout branch $branch, aborting"
  exit
fi

# Reset the code to match the tag.
git reset --hard $tag
if [ $? -ne 0 ]
then
  log_message "Failed to reset to tag, aborting"
  exit
fi

# Taking backup now.
mkdir -p "$backup_directory"
drush --root=$docroot acsf-tools-dump --result-folder=$backup_directory -y --gzip &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message "Failed to take backup, aborting"
  exit
fi

# Enable maintenance mode if mode is updb.
if [ "$mode" = "updb" ]
then
  drush --root=$docroot sfmlc alshaya-enable-maintenance &>> ${log_file}
  if [ $? -ne 0 ]
  then
    log_message "Failed to enable maintenance mode, aborting"
    exit
  fi
fi

# Force the push to avoid issues with previous commit history.
git push origin $branch -f &>> ${log_file}
if [ $? -ne 0 ]
then
  log_message "Failed to deploy code, aborting"
  exit
fi

# Sleep for 60 seconds before saying it is done.
# Code deployment to servers post git push takes time.
sleep 60

log_message "Code deployment finished"

if [ "$mode" = "updb" ]
then
  for site in `drush --root=$docroot acsf-tools-list | grep -v " "`
  do
    drush --root=$docroot -l "${site}${base_uri}" cc drush -y &>> ${log_file}
    drush --root=$docroot -l "${site}${base_uri}" updb -y &>> ${log_file}
    if [ $? -ne 0 ]
    then
      log_message "$site: UPDB FAILED, site kept offline still, please check logs"
    else
      drush --root=$docroot -l "${site}${base_uri}" alshaya-disable-maintenance &>> ${log_file}
      log_message "$site: UPDB done and site put back online"
    fi

  done
fi

if [ "$mode" = "hotfix_crf" ]
then
  log_message "Doing CRF now as requested"
  drush --root=$docroot sfml crf --delay=10 &>> ${log_file}
fi
