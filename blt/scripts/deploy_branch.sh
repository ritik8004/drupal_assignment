#!/bin/bash

set -ev

if [[ ! $TRAVIS_BRANCH =~ ^revert-.* ]]; then
  source $BLT_DIR/scripts/travis/deploy_branch;
else
  echo "We don't deploy because it is a revert branch.";
fi

set +v