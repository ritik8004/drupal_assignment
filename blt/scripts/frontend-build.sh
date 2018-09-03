#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

sTravis=0
isTravisPr=0
isTravisMerge=0
diff=""

ignoredDirs=( "alshaya_mothercare" "alshaya_amp_mothercare" "alshaya_example_subtheme" "node_modules" )

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

      # Ignore some directories which are not themes or which not be build.
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

      # We build the theme if:
      # - We are outside Travis context.
      # - The theme has changed.
      # - We are merging but the theme (css) does not exist on deploy directory.
      build=0
      if [ $(echo "$diff" | grep themes/custom/$theme_type_dir/$theme_dir) ]; then
        echo "Build $theme_type_dir because there is some change in this folder."
        setup=1
      elif [ $isTravis == 0 ]; then
        echo "Build $theme_type_dir because it is outside Travis."
        build=1
      elif [ $isTravisMerge == 1 ]; then
        if ([ $theme_type_dir == "non_transac" ])
        then
          if [[ $theme_dir == "whitelabel" && ! -d "$docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components/dist" ]]
          then
            echo -en "Building $theme_dir because there is no dist folder in $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components"
            build=1
          elif [[ $theme_dir != "whitelabel" && ! -d "$docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/dist" ]]
          then
            echo -en "Building $theme_dir because there is no dist folder in $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir"
            build=1
          fi
        elif ([ ! -d "$docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/css" ])
        then
          echo -en "Building $theme_dir because there is no css folder in $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir"
          build=1
        fi
      fi

      if ([ $build == 1 ])
      then
        echo -en "travis_fold:start:FE-$theme_dir-Build\r"
        echo -en "Start - Building $theme_dir theme"
        cd $docrootDir/themes/custom/$theme_type_dir/$theme_dir
        npm run build
        echo -en "End - Building $theme_dir theme"
        echo -en "travis_fold:end:FE-$theme_dir-Build\r"
      else
        # If the theme has not changed are we are on a merge, we copy the css
        # or dist folder from deploy (it contains the source from acquia git).
        if ([ $isTravisMerge == 1 ])
        then
          if ([ $theme_type_dir == "non_transac" ])
          then
            if [[ $theme_dir == "whitelabel" ]]
            then
              cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components/dist $docrootDir/themes/custom/$theme_type_dir/$theme_dir/components/
              echo "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied components/dist folder from deploy directory."
            else
              cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/dist $docrootDir/themes/custom/$theme_type_dir/$theme_dir/
          echo "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied dist folder from deploy directory."
            fi
          else
            cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/css $docrootDir/themes/custom/$theme_type_dir/$theme_dir/
            echo "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied css folder from deploy directory."
          fi
        else
          echo "No need to build $theme_dir theme. There is no change in $theme_dir theme."
        fi
      fi
    done

  done
else
  echo "No need to build any theme. There is no frontend change at all."
fi
