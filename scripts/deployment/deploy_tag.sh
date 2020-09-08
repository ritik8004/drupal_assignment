#!/bin/bash

# This script must be executed from inside production server.
# Command usage: deploy_tag.sh TAG MODE
# Example for updb mode: deploy_tag.sh 5.6.0-build updb
# Example for hotfix mode: deploy_tag.sh 5.6.1-build hotfix
# Example for hotfix mode and do CRF at the end: deploy_tag.sh 5.6.1-build hotfix_crf

tag="$1"
mode="$2"

if [ -z "$tag" -o -z "$mode" ]
then
  echo "Tag to deploy and deployment mode are required."
  echo "Command usage: deploy_tag.sh TAG MODE"
  echo "Example for updb mode: deploy_tag.sh 5.6.0-build updb"
  echo "Example for hotfix mode: deploy_tag.sh 5.6.1-build hotfix"
  echo "Example for hotfix mode and do CRF at the end: deploy_tag.sh 5.6.1-build hotfix_crf"
  exit
fi

stack=`whoami`
repo="$stack@svn-25.enterprise-g1.hosting.acquia.com:$stack.git"

docroot="/var/www/html/$AH_SITE_NAME/docroot"
log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-deployments.log

echo "Starting deployment in mode $mode at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}
echo

backup_directory="~/$AH_SITE_ENVIRONMENT/backup/pre-$tag"
directory="~/$AH_SITE_ENVIRONMENT/repo"

# Create folder and clone if not available
if [ ! -d "$directory/$stack" ]
then
  echo "Repo directory not available, creating and cloning. Tag $tag, Stack $stack" | tee -a ${log_file}
  mkdir -p $directory
  cd $directory
  git clone $repo
fi

if [ ! -d "$directory/$stack" ]
then
  echo "Repo directory not available still, aborting. Tag $tag, Stack $stack" | tee -a ${log_file}
  exit
fi

cd "$directory/$stack"

# Fetch all tags.
git fetch origin --tags

# Validate if tag exists.
if [ ! $(git tag -l "$tag") ]; then
  echo "Error: Tag not found, aborting. Tag $tag, Stack $stack" | tee -a ${log_file}
  exit
fi

# Checkout "main" branch used for deployment.
git checkout main

# Reset the code to match the tag.
git reset --hard $tag

# Taking backup now.
mkdir -p "$backup_directory"
drush --root=$docroot acsf-tools-dump --result-folder=$backup_directory -y --gzip 2>&1 | tee -a ${log_file}

# Enable maintenance mode if mode is updb.
if [ "$mode" = "updb" ]
then
  drush --root=$docroot sfmlc alshaya-enable-maintenance 2>&1 | tee -a ${log_file}
  echo "Sites put offline. Tag $tag, Stack $stack" | tee -a ${log_file}
fi

# Force the push to avoid issues with previous commit history.
git push origin main | tee -a ${log_file}

echo "Code deployment finished at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}

if [ "$mode" = "updb" ]
then
  for site in `drush --root=$docroot acsf-tools-list | grep -v " "`
  do
    drush --root=$docroot -l $site.factory.alshaya.com updb 2>&1 | tee -a ${log_file}
    drush --root=$docroot -l $site.factory.alshaya.com alshaya-disable-maintenance 2>&1 | tee -a ${log_file}
    echo "UPDB done and site put back online for $site at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}
  done

  echo "UPDB done and all sites put back online at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}
fi

if [ "$mode" = "hotfix_crf" ]
then
  echo "Doing CRF now as requested. Tag $tag, Stack $stack" | tee -a ${log_file}
  drush --root=$docroot sfml crf --delay=10 2>&1 | tee -a ${log_file}
fi
