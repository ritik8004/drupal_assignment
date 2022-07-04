#!/bin/bash

scriptDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
bltDir="$scriptDir/../../blt"

# Fetch the list of remotes from the blt.yml config file.
repos=`grep -Ei '@svn-5975.enterprise-g1.hosting.acquia.com' ${bltDir}/blt.yml | sed -r "s/'//g" | sed -r "s/- //g"`

# Get the first repo from the list.
repo1=`echo ${repos} | cut -d" " -f1`

# Clone the first repo in a temporary directory.
rm -Rf /tmp/alshaya_acquia_git
echo "Cloning $repo1 into /tmp/alshaya_acquia_git."
git clone $repo1 /tmp/alshaya_acquia_git
cd /tmp/alshaya_acquia_git || exit

# Add the other repos as git remotes.
for repo in $repos ; do
  repo_name=$(echo $repo | cut -d '@' -f1)
  echo "Adding $repo as $repo_name remote."
  git remote add $repo_name $repo
done

echo "Settings git configs."
git config user.name "Github-Actions-CI"
git config user.email "noreply@github.com"
git config checkout.defaultRemote origin
git config advice.detachedHead false

echo "Fetching all remotes."
git pull --all

# Fetch the list of branches we want to reset.
refs=$(git ls-remote -h $repo1 | grep -o -E "refs/heads/.*-build$")
refs+=" distro"

# Reset the history of each branch and push to all repos.
for ref in $refs ; do
  ref_name=$(echo $ref | cut -d '/' -f3)
  echo "Processing branch $ref_name."

  git checkout $ref_name
  git checkout --orphan $ref_name-tmp
  git add .
  git commit -m "Starting fresh orphan branch for $ref_name" --quiet
  git branch -D $ref_name
  git branch -m $ref_name

  # Push the reset branch to all the remotes.
  for repo in $repos ; do
    repo_name=$(echo $repo | cut -d '@' -f1)
    echo "Pushing new $ref_name to $repo_name"
    git push -f $repo_name $ref_name
  done
done

# Master is a default branch available after provisioning. It may not be safe to delete. To avoid
# too much divergent branch, we reset the master branch based on latest develop-build.
echo "Processing branch master."
git checkout develop-build
git checkout --orphan master-tmp
git add .
git commit -m "Realign master with develop-build" --quiet
git branch -D master
git branch -m master

# Push the reset branch to all the remotes.
for repo in $repos ; do
  repo_name=$(echo $repo | cut -d '@' -f1)
  echo "Pushing new master to $repo_name"
  git push -f $repo_name master
done

# Execute a git prune on all the repos.
for repo in $repos ; do
  repo_name=$(echo $repo | cut -d '@' -f1)
  git remote prune $repo_name
done
