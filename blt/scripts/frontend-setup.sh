
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
fi

# Only build any theme if we are outside of travis PR or no theme file was changed in PR
if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep /themes/) ]])
then
  docrootDir="$1"

  # Skip building particular theme if we are in PRs and the theme files were not changed
  if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep themes/custom/transac) ]])
  then
    echo -en "travis_fold:start:FE-Setup"
    echo -en "Start - Installing npm for transac themes"
    cd $docrootDir/themes/custom/transac
    npm run install-tools
    echo -en "End - Installing npm for transac themes"
  fi

  # Skip building particular theme if we are in PRs and the theme files were not changed
  if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep themes/custom/non-transac) ]])
  then
    echo -en "Start - Installing npm for non-transac themes"
    cd $docrootDir/themes/custom/non_transac
    npm run install-tools
    echo -en "End - Installing npm for non-transac themes"
  fi

  # Skip building particular theme if we are in PRs and the theme files were not changed
  if ([ $isTravisPr == 0 ]) || ([[ $(git diff --name-only $TRAVIS_BRANCH |grep themes/custom/amp) ]])
  then
    echo -en "Start - Installing npm amp themes"
    cd $docrootDir/themes/custom/amp
    npm run install-tools
    echo -en "End - Installing npm for amp themes"
    echo -en "travis_fold:end:FE-Setup"
  fi
else
  echo "No need to build frontend on PR. We are only building frontend if there are changed theme files."
fi
