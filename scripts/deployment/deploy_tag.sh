#!/bin/bash

# This script must be executed from inside production server.
# Usage: deploy_tag.sh TAG MODE
# Example mode updb: deploy_tag.sh 5.6.0 updb
# Example mode hotfix: deploy_tag.sh 5.6.1 hotfix

tag="$1"
mode="$2"
stack=`whoami`
repo="$stack@svn-25.enterprise-g1.hosting.acquia.com:$stack.git"

log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-deployments.log

echo "Starting deployment in mode $mode at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}
echo

backup_directory="~/$AH_SITE_ENVIRONMENT/backup/pre-$tag"
directory="~/$AH_SITE_ENVIRONMENT/repo"

# Create folder and clone if not available
if [ ! -d "$directory" ]
then
  echo "Repo directory not available, creating and cloning. Tag $tag, Stack $stack" | tee -a ${log_file}
  mkdir $directory
  cd $directory
  git clone $repo
fi

cd "$directory/$stack"

# Checkout master branch before doing main branch operations
git checkout master

# Delete the deployment branch from local to
# allow recreating it from tag.
git branch -D main

# Fetch all tags.
git fetch --tags

# Validate if tag exists.
if [ ! $(git tag -l "$tag") ]; then
  echo "Error: Tag not found, aborting. Tag $tag, Stack $stack" | tee -a ${log_file}
  exit
fi

# Create "main" branch from tag.
git branch main $tag

# Checkout the new "main" branch.
git checkout main

# Go to docroot to do site operations.
cd /var/www/html/$AH_SITE_NAME/docroot

if [ "$mode" = "updb" ]
then
  drush sfmlc alshaya-enable-maintenance 2>&1 | tee -a ${log_file}
  echo "Sites put offline. Tag $tag, Stack $stack" | tee -a ${log_file}
fi

# Take backup now just before deployment
mkdir -p "$backup_directory"
drush acsf-tools-dump --result-folder=$backup_directory -y --gzip 2>&1 | tee -a ${log_file}

# Come back to deploy directory.
cd "$directory/$stack"

# Force the push to avoid issues with previous commit history.
git push origin main -f | tee -a ${log_file}

echo "Code deployment finished at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}

# Go to docroot to do site operations again.
cd /var/www/html/$AH_SITE_NAME/docroot

if [ "$mode" = "updb" ]
then
  drush sfmlc updb 2>&1 | tee -a ${log_file}
  drush sfmlc alshaya-disable-maintenance 2>&1 | tee -a ${log_file}
  echo "UPDB done and sites put back online at `date`. Tag $tag, Stack $stack" | tee -a ${log_file}
fi
