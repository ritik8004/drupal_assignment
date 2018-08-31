
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

# We only setup themes on if we are not on Travis or if themes have changed.
for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
do
  theme_type_dir=${dir##*/}

  # We build the theme if:
    # - We are outside Travis context.
    # - The theme has changed.
    # - We are merging but the theme (css) does not exist on deploy directory.
  setup=0
  if [ $isTravisMerge == 1 ]; then
    for subdir in $(find $docrootDir/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
    do
      theme_dir=${subdir##*/}

      if [[ $(echo "$diff" | grep themes/custom/$theme_type_dir/$theme_dir) || ! -d "$docrootDir/themes/custom/$theme_type_dir/$theme_dir/css" ]]
      then
        setup=1
      fi
    done
  elif [ $isTravis == 0 ]; then
    setup=1
  elif [ $(echo "$diff" | grep themes/custom/$theme_type_dir) ]; then
    setup=1
  fi

  echo "setup: $setup"

  if ([ $setup == 1 ])
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
