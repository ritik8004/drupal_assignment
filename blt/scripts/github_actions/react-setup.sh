#!/bin/bash
# This file runs during the frontend setup.
# @todo execute this only when required.

set -e

docrootDir="$GITHUB_WORKSPACE/docroot"
echo $docrootDir


cd $docrootDir/modules/react
npm install

ignoredDirs=( "node_modules" "alshaya_react" "js" "dist" )

# Validate utility files.
npm run lint $docrootDir/modules/react/js/

# Validate files now.
for subdir in $(find $docrootDir/modules/react -mindepth 1 -maxdepth 1 -type d)
do
  # Ignore some directories which are not react feature modules.
  ignore=0
  for ignoredDir in "${ignoredDirs[@]}"
  do
    if ([[ $(echo "$subdir" | grep $ignoredDir) ]])
    then
      ignore=1
      break
    fi
  done

  if ([ $ignore == 1 ])
  then
    continue
  fi

  if ([ -d "$subdir/js/src" ])
  then
    npm run lint $subdir/js/src/
  else
    npm run lint $subdir/js/
  fi
done

# Run Unit tests.
npm test

if [ $? -ne 0 ]
then
  exit 1;
fi
