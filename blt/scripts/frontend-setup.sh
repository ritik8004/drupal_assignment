
#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

isTravis=0
isTravisPr=0
isTravisMerge=0

# Determine if we are on Travis.
if [[ $TRAVIS && $TRAVIS == "true" ]]; then
  isTravis=1

  if [[ $TRAVIS_PULL_REQUEST && $TRAVIS_PULL_REQUEST == "true" ]]; then
    isTravisMerge=1
    # @TODO: For now we do nothing here but if we can achieve to avoid
    # rebuilding frontend which have not changed during merge, we should
    # update the setup script to only install tools if there is a frontend to
    # build.
  else
    isTravisPr=1
    git fetch origin $TRAVIS_BRANCH:$TRAVIS_BRANCH-frontend-check
  fi
fi

# Display some log information.
echo "isTravis: $isTravis"
echo "isTravisPr: $isTravisPr"
echo "isTravisMerge: $isTravisMerge"

# We always setup frontend tools unless we are
if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH-frontend-check | grep /themes/) ]])
then
  for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
  do
    theme_type_dir=${dir##*/}

    # Skip setup particular theme type if we are in PRs and the theme files were not changed
    if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH-frontend-check | grep themes/custom/$theme_type_dir) ]])
    then
      echo -en "travis_fold:start:FE-$theme_type_dir-Setup\r"
      echo -en "Start - Installing npm for $theme_type_dir themes"
      cd $docrootDir/themes/custom/$theme_type_dir
      npm run install-tools
      echo -en "End - Installing npm for $theme_type_dir themes"
      echo -en "travis_fold:end:FE-$theme_type_dir-Setup\r"
    else
      echo "No need to setup $theme_type_dir frontend. There is no change in any $theme_type_dir themes."
    fi
  done
else
  echo "No need to setup any frontend. There is no frontend change at all."
fi
