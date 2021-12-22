#!/bin/bash
# This file runs during the frontend setup.
# @todo reduce the execution of this to only when required.

set -e

docrootDir="$GITHUB_WORKSPACE/docroot"
echo $docrootDir

# We only setup themes if themes have changed.
for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
do
  theme_type_dir=${dir##*/}

  cd $docrootDir/themes/custom/$theme_type_dir
  npm run install-tools

  # TODO: Increase test coverage to all the themes.
  # Validate only for travis PRs.
  if [[ $theme_type_dir == 'transac' ]]
  then
    ignoredDirs=( "alshaya_example_subtheme" "node_modules" )

    for subdir in $(find $docrootDir/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
    do
      theme_dir=${subdir##*/}
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

      cd $docrootDir/themes/custom/$theme_type_dir/$theme_dir
      gulp lint:css-with-fail
      gulp lint:js-with-fail
      if [ -d $docrootDir/themes/custom/$theme_type_dir/$theme_dir/conditional-sass ];
      then
        gulp lint:module-component-libraries-css-with-fail
      fi
    done
  fi

done
