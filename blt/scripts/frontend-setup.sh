
#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

isTravis=0
isTravisPr=0
isTravisMerge=0
diff=""

# Determine if we are on Travis.
if [[ $TRAVIS && $TRAVIS == "true" ]]; then
  isTravis=1

  if [[ $TRAVIS_PULL_REQUEST && $TRAVIS_PULL_REQUEST == "false" ]]; then
    isTravisMerge=1
  else
    isTravisPr=1
    git fetch origin $TRAVIS_BRANCH:$TRAVIS_BRANCH-frontend-check
    diff=$(git diff --name-only $TRAVIS_BRANCH-frontend-check)
  fi
fi

# We only setup themes on if we are not on Travis or if themes have changed.
for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
do
  theme_type_dir=${dir##*/}

  echo -en "travis_fold:start:FE-$theme_type_dir-Setup\r"

  # We build the theme if:
    # - We are outside Travis context.
    # - The theme has changed.
    # - We are merging but the theme (css) does not exist on deploy directory.
  setup=0
  if [ $isTravisMerge == 1 ]; then
    echo -en "Setup $theme_type_dir because we are merging a PR."
    setup=1
  elif [ $isTravis == 0 ]; then
    echo -en "Setup $theme_type_dir because it is outside Travis."
    setup=1
  elif ([[ $(echo "$diff" | grep themes/custom/$theme_type_dir) ]]); then
    echo -en "Setup $theme_type_dir because there is some change in this folder."
    setup=1
  fi

  if ([ $setup == 1 ])
  then
    cd $docrootDir/themes/custom/$theme_type_dir
    npm run install-tools

    # TODO: Increase test coverage to all the themes.
    if ([ $theme_type_dir == 'transac' ])
    then
      gulp lint:css-with-fail
      gulp lint:js-with-fail
    fi
  else
    echo -en "No need to setup $theme_type_dir frontend. There is no change in any $theme_type_dir themes."
  fi

  echo -en "travis_fold:end:FE-$theme_type_dir-Setup\r"
done
