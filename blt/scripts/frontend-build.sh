#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

transac=( "alshaya_white_label" "alshaya_hnm" "pottery_barn_non_trans" "alshaya_pottery_barn" "alshaya_victoria_secret" "alshaya_bathbodyworks" )
non_transac=( "debenhams" "whitelabel" "whitelabel_non_transac" "victoria_secret" "bath_body_works" "bouchon_bakery" )
amp=( "alshaya_amp_white_label" "alshaya_amp_hnm" "alshaya_amp_victoria_secret")

# This evaluates if we are inside of travis PR
# This script is used by blt, hence firstly the test around the variable existing
# Then the second part is set to true if PR is invoked from travis (otherwise it's deployment)
if ([ -z "$TRAVIS_PULL_REQUEST" ]) || ([ $TRAVIS_PULL_REQUEST == "false" ])
then
  isTravisPr=0
else
  isTravisPr=1
fi

# Only build any theme if we are outside of travis PR or no theme file was changed in PR
if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep /themes/) ]])
then
  for i in "${transac[@]}"
  do
    # Skip building particular theme if we are in PRs and the theme files were not changed
    if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep themes/custom/transac/$i) ]])
    then
      echo -en "travis_fold:start:FE-Build-${i}\r"
      cd $docrootDir/themes/custom/transac/$i
      npm run build
      echo -en "travis_fold:end:FE-Build-${i}\r"
    fi
  done

  for i in "${non_transac[@]}"
  do
    # Skip building particular theme if we are in PRs and the theme files were not changed
    if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep themes/custom/non_transac/$i) ]])
    then
      echo -en "travis_fold:start:FE-Build-${i}\r"
      cd $docrootDir/themes/custom/non_transac/$i
      npm run build
      echo -en "travis_fold:end:FE-Build-${i}\r"
    fi
  done

  for i in "${amp[@]}"
  do
    # Skip building particular theme if we are in PRs and the theme files were not changed
    if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep themes/custom/amp/$i) ]])
    then
      echo -en "travis_fold:start:FE-Build-${i}\r"
      cd $docrootDir/themes/custom/amp/$i
      npm run build
      echo -en "travis_fold:end:FE-Build-${i}\r"
    fi
  done
else
  echo "No need to build frontend on PR. We are only building frontend if there are changed theme files."
fi
