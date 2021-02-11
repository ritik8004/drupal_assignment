#!/bin/bash

set -ev

# Do nothing for cron job.
if ([ $TRAVIS_EVENT_TYPE = "cron" ])
then
  exit 0
fi

source $BLT_DIR/scripts/travis/deploy_branch;

set +v
