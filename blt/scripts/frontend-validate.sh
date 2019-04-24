#!/bin/bash
# This file runs during the frontend validate.

set -e

docrootDir="$1"

# TODO: This task seems not defined in non-transac sites.
# Also not doing this for amp as of now.

# List of folders into themes/custom/transac which must be ignored.
ignoredDirs=( "alshaya_example_subtheme" "node_modules" )

for subdir in $(find $docrootDir/themes/custom/transac -mindepth 1 -maxdepth 1 -type d)
do
  theme_dir=${subdir##*/}

  # Ignore some directories which are not themes (node_modules) or which
  # don't need to be validated (alshaya_example_subtheme or mothercare themes).
  ignore=0
  for ignoredDir in "${ignoredDirs[@]}"
  do
    if ([[ $(echo "$theme_dir" | grep $ignoredDir) ]])
    then
      ignore=1
    fi
  done

  if ([ $ignore == 1 ])
  then
    continue
  fi

  cd $docrootDir/themes/custom/transac/$theme_dir
  gulp lint:css-with-fail
  gulp lint:js-with-fail

done

exit $?
