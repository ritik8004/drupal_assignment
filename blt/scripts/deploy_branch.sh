#!/bin/bash

set -ev

if [[ ! $TRAVIS_BRANCH =~ ^revert-.* ]]; then
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
    echo ">>>>>>> We don't deploy because this branch is not deployed anywhere.";
  else
    source $BLT_DIR/scripts/travis/deploy_branch;
  fi
else
  echo ">>>>>>> We don't deploy because it is a revert branch.";
fi

set +v
