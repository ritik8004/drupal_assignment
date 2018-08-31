#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

sTravis=0
isTravisPr=0
isTravisMerge=0

# Determine if we are on Travis.
if [[ $TRAVIS && $TRAVIS == "true" ]]; then
  isTravis=1

  if [[ $TRAVIS_PULL_REQUEST && $TRAVIS_PULL_REQUEST == "true" ]]; then
    isTravisMerge=1
  else
    isTravisPr=1
    git fetch origin $TRAVIS_BRANCH:$TRAVIS_BRANCH-frontend-check
  fi
fi

# Display some log information.
echo "isTravis: $isTravis"
echo "isTravisPr: $isTravisPr"
echo "isTravisMerge: $isTravisMerge"

# Only build any theme if we are outside of travis PR or no theme file was changed in PR
if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH-frontend-check | grep /themes/) ]])
then
  for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
  do
    theme_type_dir=${dir##*/}

    for subdir in $(find $docrootDir/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
    do
      theme_dir=${subdir##*/}

      if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH-frontend-check | grep themes/custom/$theme_type_dir/$theme_dir) ]])
      then
        echo -en "travis_fold:start:FE-$theme_dir-Build\r"
        cd $docrootDir/themes/custom/$theme_type_dir/$theme_dir
        npm run build
        echo -en "travis_fold:end:FE-$theme_dir-Build\r"
      else
        echo "No need to build $theme_dir theme. There is no change in $theme_dir theme."
        # @TODO: In case isTravisMerge=1 and no change in this theme, we should
        # not build the it but simply copy the css directory from deploy.
      fi
    done

  done
else
  echo "No need to build any theme. There is no frontend change at all."
fi
