#!/usr/bin/env bash

set -ev

# Use the branch name from argument or use default from GITHUB variable.
BRANCH_NAME=${1:-$GITHUB_REF_NAME}

# Added to fix dubious ownership issue in repository.
git config --global --add safe.directory '*'

$BLT_DIR/bin/blt artifact:deploy --commit-msg "Automated commit by Github Actions for Build $GITHUB_RUN_NUMBER of workflow $GITHUB_WORKFLOW" --branch "$BRANCH_NAME-build" --no-interaction --verbose

set +v
