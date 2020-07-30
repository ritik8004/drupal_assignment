
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

# We only setup if we are not on Travis or if it is changed.
echo -en "travis_fold:start:REACT-Setup\r"

# We build if:
  # - We are outside Travis context.
  # - Modules have changed.
  # - We are merging but the dist dir does not exist on deploy directory.
setup=0
if [ $isTravisMerge == 1 ]; then
  echo -en "Setup REACT because we are merging a PR."
  setup=1
elif [ $isTravis == 0 ]; then
  echo -en "Setup REACT because it is outside Travis."
  setup=1
elif ([[ $(echo "$diff" | grep modules/react) ]]); then
  echo -en "Setup REACT because there are changes in this folder."
  setup=1
fi

if ([ $setup == 1 ])
then
  cd $docrootDir/modules/react
  npm install

  # Validate files now.
  npm run lint alshaya_spc/js/
else
  echo -en "No need to setup REACT. There is no change in any modules/react."
fi

echo -en "travis_fold:end:REACT-Setup\r"
