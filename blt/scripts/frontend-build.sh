#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

sTravis=0
isTravisPr=0
isTravisMerge=0
diff=""

# Determine if we are on Travis.
if [[ $TRAVIS && $TRAVIS == "true" ]]; then
  isTravis=1

  if [[ $TRAVIS_PULL_REQUEST && $TRAVIS_PULL_REQUEST == "false" ]]; then
    isTravisMerge=1
    diff=$(git whatchanged -n 1 --name-only)
  else
    isTravisPr=1
    git fetch origin $TRAVIS_BRANCH:$TRAVIS_BRANCH-frontend-check
    diff=$(git diff --name-only $TRAVIS_BRANCH-frontend-check)
  fi
fi

# Display some log information.
echo "isTravis: $isTravis"
echo "isTravisPr: $isTravisPr"
echo "isTravisMerge: $isTravisMerge"

# We always build themes unless we are testing a simple push on Travis and there is no change in themes.
if ([ $isTravis == 0 ]) || ([ $isTravisMerge == 1 ]) || ([[ $(echo "$diff" | grep /themes/) ]])
then
  for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
  do
    theme_type_dir=${dir##*/}

    for subdir in $(find $docrootDir/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
    do
      theme_dir=${subdir##*/}

      # We build the theme if:
      # - We are outside Travis context.
      # - The theme has changed.
      # - We are merging but the theme (css) does not exist on deploy directory.
      if ([ $isTravis == 0 ]) || ([[ $(echo "$diff" | grep themes/custom/$theme_type_dir/$theme_dir) ]]) || ([[ $isTravisMerge == 1 && ! -d "$docrootDir/themes/custom/$theme_type_dir/$theme_dir/css" ]])
      then
        echo -en "travis_fold:start:FE-$theme_dir-Build\r"
        cd $docrootDir/themes/custom/$theme_type_dir/$theme_dir
        npm run build
        echo -en "travis_fold:end:FE-$theme_dir-Build\r"
      else
        # If the theme has not changed are we are on a merge, we copy the css
        # folder from deploy (it contains the source from acquia git).
        if ([ $isTravisMerge == 1 ])
        then
          cp -r $docrootDir/themes/custom/$theme_type_dir/$theme_dir/css $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/
          echo "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied css folder from deploy directory."
        else
          echo "No need to build $theme_dir theme. There is no change in $theme_dir theme."
        fi
      fi
    done

  done
else
  echo "No need to build any theme. There is no frontend change at all."
fi
