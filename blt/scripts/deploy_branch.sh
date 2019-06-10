#!/bin/bash

set -ev

if [[ ! $TRAVIS_BRANCH =~ ^revert-.* ]]; then
  # We can force a build using:
  # branch=xxx
  # git fetch upstream
  # git checkout $branch
  # git reset --hard upstream/$branch
  # git commit --allow-empty -m "BUILD REQUEST" -n
  # git push upstream $branch 
  if [ "$TRAVIS_COMMIT_MESSAGE" = "BUILD REQUEST" ]; then
    echo "Forced build request"
  else
    echo "username=$acsf_api_username" > $HOME/acsf_api_settings
    echo "api_key=$acsf_api_key" >> $HOME/acsf_api_settings

    deployed_branches=$(${BUILD_DIR}/scripts/git_cleanup/get-deployed-branches.sh | cut -d' ' -f2)
    echo "Deployed branches:"
    echo $deployed_branches
    echo

    branch="$TRAVIS_BRANCH-build"
    for deployed_branch in $deployed_branches ; do
      if [ "$branch" = "$deployed_branch" ] ; then
        break
      fi
    done

    if [ ! "$branch" = "$deployed_branch" ] ; then
      echo ">>>>>>> We don't deploy because $branch is not deployed anywhere.";
      exit
    fi
  fi

  source $BLT_DIR/scripts/travis/deploy_branch;
else
  echo ">>>>>>> We don't deploy because it is a revert branch.";
fi

set +v
