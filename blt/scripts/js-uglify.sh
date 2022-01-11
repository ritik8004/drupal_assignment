#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

isTravis=0
isTravisMerge=0
diff=""

# Determine if we are on Travis.
if [[ $TRAVIS && $TRAVIS == "true" ]]; then
  isTravis=1

  if [[ $TRAVIS_PULL_REQUEST && $TRAVIS_PULL_REQUEST == "false" ]]; then
    isTravisMerge=1
    log=$(git log -n 1)

    # Extract commit IDs from git log.
    re="Merge: ([abcdef0-9]{7,10}) ([abcdef0-9]{7,10})"

    if [[ $log =~ $re ]]; then
      # Get a list of updated files in this PR.
      diff=$(git diff ${BASH_REMATCH[1]} ${BASH_REMATCH[2]} --name-only)
    else
      isTravis=0
    fi

    # If the PR title or merge contains "FORCE" we will build all JS files.
    if ([[ $(echo "$log" | grep "FORCE") ]])
    then
      isTravis=0
    fi
  else
    diff=$(git diff --name-only $TRAVIS_BRANCH-frontend-check)
  fi
fi

# We always build themes unless we are testing a simple push on Travis and there is no change in JS files.
if ([ $isTravis == 0 ]) || ([ $isTravisMerge == 1 ])
then
  # Prepare the js and composer filter to track the js and core/contrib changes.
  jsFilter=''
  composerFilter=''
  # We build the JS uglification if:
    # - If there are changes in JS file.
    # - If we are doing update in core or contrib modules.
    # - If build folder is missing that means uglification is not done.
  if ([[ $(echo "$diff" | grep /js/) ]]) then
    jsFilter=$(echo "$diff" | grep /js/)
  elif ([[ $(echo "$diff" | grep composer) ]]) then
    composerFilter=$(echo "$diff" | grep composer)
  else
    echo -en "No need to build any JS. There is no frontend change at all."
  fi
  # Verify if build folder exists or some changes in composer file.
  if ([ ! -d "$docrootDir/build" ]) || ([[ $composerFilter ]])
  then
    cd $docrootDir
    npm run build
  elif [[ $jsFilter ]]
  then
    for path in $jsFilter
    do
      cd $docrootDir
      npm run build -- --path=${path#docroot/}
    done
  else
    echo -en "No need to build any JS. There is no frontend change at all."
  fi
else
  echo -en "No need to build any JS. There is no frontend change at all."
fi
