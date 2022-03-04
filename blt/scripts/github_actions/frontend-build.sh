#!/usr/bin/env bash
# This file runs during the frontend build.

set -e

docrootDir="$1"
diff=""
ignoredDirs=( "alshaya_example_subtheme" "node_modules" "gulp-tasks")

# Determine if we are on Github Actions.
if [[ $GITHUB_ACTIONS == "true" ]]; then
  # Fetch the last commit from git log.
  log=$(git log -n 1)

  # Pattern for Merge commit.
  re="Merge: ([abcdef0-9]{7,8}) ([abcdef0-9]{7,8})"

  # Check if last commit was a merge commit.
  if [[ $log =~ $re ]]; then
    # Get a list of updated files in this merge commit.
    diff=$(git diff ${BASH_REMATCH[1]} ${BASH_REMATCH[2]} --name-only)

    # Looping through theme types (transac, non-transac etc.)
    for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
    do
      theme_type_dir=${dir##*/}

      # Ignore all amp themes. Condition can be removed after
      # https://alshayagroup.atlassian.net/browse/CORE-37614 is done.
      # Also ignore node_modules folder inside docroot/themes/custom.
      if ([[ $(echo "$theme_type_dir" | grep amp) ]] || [[ $(echo "$theme_type_dir" | grep node_modules) ]])
      then
        continue
      fi

      echo -en "\nStarting build operation for all $theme_type_dir themes...\n"

      # Looping through theme folders inside theme types and checking
      # if theme needs to be rebuilt or can be copied from acquia repo.
      for subdir in $(find $docrootDir/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
      do
        theme_dir=${subdir##*/}
        # Ignore some directories which are not themes (node_modules) or which
        # don't need to be built (alshaya_example_subtheme themes).
        ignore=0
        for ignoredDir in "${ignoredDirs[@]}"
        do
          if ([[ $(echo "$theme_dir" | grep $ignoredDir) ]])
          then
            ignore=1
          fi
        done

        # Ignore themes not having gulp file.
        if [ ! -f $docrootDir/themes/custom/$theme_type_dir/$theme_dir/gulpfile.js ]; then
          echo -en "\n$theme_dir seems to not be a valid theme. No gulpfile.js. Not building.\n"
          ignore=1
        fi

        # Skip theme building if ignored.
        if ([ $ignore == 1 ])
        then
          echo -en "Skipping $theme_dir folder.\n"
          continue
        fi

        echo -en "\nStarting build operation for $theme_dir theme...\n"

        # We build the theme if:
        # - The theme has changed.
        # - The parent theme has changed.
        build=0
        if ([[ $(echo "$diff" | grep themes/custom/$theme_type_dir/$theme_dir) ]]); then
          echo -en "Building $theme_dir because there is some change in this folder.\n"
          build=1
        elif ([[ $theme_type_dir == "transac" && $(echo "$diff" | grep themes/custom/$theme_type_dir/alshaya_white_label) ]]); then
          echo -en "Building $theme_dir because the parent theme (alshaya_white_label) has changed.\n"
          build=1
        elif ([[ $theme_type_dir == "non_transac" && $(echo "$diff" | grep themes/custom/$theme_type_dir/whitelabel) ]]); then
          echo -en "Building $theme_dir because the parent theme (whitelabel) has changed.\n"
          build=1
        elif ([[ $theme_type_dir == "amp" && $(echo "$diff" | grep themes/custom/$theme_type_dir/alshaya_amp_white_label) ]]); then
          echo -en "Building $theme_dir because the parent theme (alshaya_amp_white_label) has changed.\n"
          build=1
        fi

        # We also build the theme if css or dist or the theme itself
        # is not present in acquia repo (ex: new created themes).
        if ([ $theme_type_dir == "non_transac" ])
        then
          if [[ $theme_dir == "whitelabel" && ! -d "$docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components/dist" ]]
          then
            echo -en "Building $theme_dir because there is no dist folder in $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components\n"
            build=1
          elif [[ $theme_dir != "whitelabel" && ! -d "$docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/dist" ]]
          then
            echo -en "Building $theme_dir because there is no dist folder in $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir\n"
            build=1
          fi
        elif ([ ! -d "$docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/css" ])
        then
          echo -en "Building $theme_dir because there is no css folder in $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir\n"
          build=1
        fi

        # Finally build the theme if change detected.
        if ([ $build == 1 ])
        then
          cd $docrootDir/themes/custom/$theme_type_dir/$theme_dir
          npm run build
        else
          # If the theme has not changed, we copy the css or dist
          # folder from deploy (it contains the source from acquia git).
          if ([ $theme_type_dir == "non_transac" ])
          then
            if [[ $theme_dir == "whitelabel" ]]
            then
              cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components/dist $docrootDir/themes/custom/$theme_type_dir/$theme_dir/components/
              echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied components/dist folder from deploy directory.\n"
            else
              cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/dist $docrootDir/themes/custom/$theme_type_dir/$theme_dir/
              echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied dist folder from deploy directory.\n"
            fi
          else
            cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/css $docrootDir/themes/custom/$theme_type_dir/$theme_dir/
            echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied css folder from deploy directory.\n"
          fi
        fi
        echo -en "Finishing build operation for $theme_dir theme...\n"
      done
        echo -en "\nFinished building all $theme_type_dir themes...\n\n"
    done
  else
    # Build all themes if could not differentiate changed
    # files in merge commit.
    vendor/bin/blt alshayafe:build-all-themes
  fi
else
  # Build all themes if outside of Github Actions.
  vendor/bin/blt alshayafe:build-all-themes
fi
