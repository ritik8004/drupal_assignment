
#!/bin/bash
# This file runs during the frontend setup.

set -e

# This evaluates if we are inside of travis PR
# This script is used by blt, hence firstly the test around the variable existing
# Then the second part is set to true if PR is invoked from travis (otherwise it's deployment)
if ([ -z "$TRAVIS_PULL_REQUEST" ]) || ([ $TRAVIS_PULL_REQUEST == "false" ])
then
  isTravisPr=0
else
  isTravisPr=1
  git fetch origin $TRAVIS_BRANCH:$TRAVIS_BRANCH-frontend-check
fi

docrootDir="$1"

echo "isTravisPr: $isTravisPr"

# Only setup any theme type if we are outside of travis PR or no theme file was changed in PR
if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH-frontend-check | grep /themes/) ]])
then
  for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
  do
    theme_dir=${dir##*/}

    # Skip setup particular theme type if we are in PRs and the theme files were not changed
    if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH-frontend-check | grep themes/custom/$theme_dir) ]])
    then
      echo -en "travis_fold:start:FE-$theme_dir-Setup\r"
      echo -en "Start - Installing npm for $theme_dir themes"
      cd $docrootDir/themes/custom/$theme_dir
      npm run install-tools
      echo -en "End - Installing npm for $theme_dir themes"
      echo -en "travis_fold:end:FE-$theme_dir-Setup\r"
    fi
  done
else
  echo "No need to build frontend on PR. We are only building frontend if there are changed theme files."
fi
