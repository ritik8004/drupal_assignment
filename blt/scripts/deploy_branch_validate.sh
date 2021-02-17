#!/bin/bash

set -ev

echo "CI_TARGET_BRANCH: $CI_MERGE_REQUEST_TARGET_BRANCH_NAME"
echo "CI_COMMIT_TAG: $CI_COMMIT_TAG"
echo "CI_COMMIT_MESSAGE: $CI_COMMIT_MESSAGE"
echo "CI_MERGE_REQUEST: $CI_MERGE_REQUEST_ID"
echo ""

# Run this script only for merge.
if [ ! "CI_MERGE_REQUEST" = "false" ]
then
  echo "Not a Gitlab Merge Build, no validation required."
  exit 0
fi

if [ ! "$CI_COMMIT_TAG" = "" ]
then
  echo "Gitlab Build request for tag, no validation required."
  exit 0
fi

if [[ ! $CI_MERGE_REQUEST_TARGET_BRANCH_NAME =~ ^revert-.* ]]; then
  # We can force a build using:
  # branch=xxx
  # git fetch upstream
  # git checkout $branch
  # git reset --hard upstream/$branch
  # git commit --allow-empty -m "BUILD REQUEST" -n
  # git push upstream $branch
  if [ "$CI_COMMIT_MESSAGE" = "BUILD REQUEST" ]; then
    echo "Forced build request"
    exit 0
  fi

  echo "username=$acsf_api_username" > $HOME/acsf_api_settings
  echo "api_key=$acsf_api_key" >> $HOME/acsf_api_settings

  deployed_branches=$(${BUILD_DIR}/scripts/git_cleanup/get-deployed-branches.sh | cut -d' ' -f2)
  echo "Deployed branches:"
  echo $deployed_branches
  echo

  branch="$CI_MERGE_REQUEST_TARGET_BRANCH_NAME-build"
  for deployed_branch in $deployed_branches ; do
    if [ "$branch" = "$deployed_branch" ] ; then
      exit 0
    fi
  done

  echo ">>>>>>> We don't deploy because $branch is not deployed anywhere.";
  exit 1
else
  echo ">>>>>>> We don't deploy because it is a revert branch.";
  exit 1
fi

set +v
