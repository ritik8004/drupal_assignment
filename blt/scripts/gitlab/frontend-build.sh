#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

isGitlab=0
isGitlabPr=0
isGitlabMerge=0
diff=""

ignoredDirs=( "alshaya_example_subtheme" "node_modules" )

# Determine if we are on Gitlab.
if [[ $CI && $CI ]]; then
  isGitlab=1

  if [[ $CI_PROJECT_NAME && $CI_PROJECT_NAME == "alshaya-pso" ]]; then
    isGitlabMerge=1
    log=$(git log -n 1)

    # Extract commit IDs from git log.
    re="Merge: ([abcdef0-9]{7,8}) ([abcdef0-9]{7,8})"

    if [[ $log =~ $re ]]; then
      # Get a list of updated files in this PR.
      diff=$(git diff ${BASH_REMATCH[1]} ${BASH_REMATCH[2]} --name-only)
    else
      isGitlab=0
      echo "Not able to identify commit IDs to do the diff. Building all the themes."
    fi

    # If the PR title or merge contains "FORCE" we will build all themes.
    if ([[ $(echo "$log" | grep "FORCE") ]])
    then
      isGitlab=0
    fi
  else
    isGitlabMerge=1
    diff=$(git diff --name-only $CI_MERGE_REQUEST_TARGET_BRANCH_NAME-frontend-check)
  fi
fi

# We always build themes unless we are testing a simple push on Travis and there is no change in themes.
if ([ $isGitlab == 0 ]) || ([ $isGitlabMerge == 1 ]) || ([[ $(echo "$diff" | grep /themes/) ]])
then
  for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
  do
    theme_type_dir=${dir##*/}

    for subdir in $(find $docrootDir/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
    do
      theme_dir=${subdir##*/}

      echo -en "gitlab_fold:start:FE-$theme_dir-Build\r"

      # Ignore some directories which are not themes (node_modules) or which
      # don't need to be build (alshaya_example_subtheme or mothercare themes).
      ignore=0
      for ignoredDir in "${ignoredDirs[@]}"
      do
        if ([[ $(echo "$theme_dir" | grep $ignoredDir) ]])
        then
          ignore=1
        fi
      done

      if [ ! -f $docrootDir/themes/custom/$theme_type_dir/$theme_dir/gulpfile.js ]; then
        echo -en "$theme_dir seems to not be a valid theme. No gulpfile.js. Not building."
        ignore=1
      fi

      if ([ $ignore == 1 ])
      then
        continue
      fi

      # We build the theme if:
      # - We are outside Travis context.
      # - The theme has changed.
      # - The parent theme has changed.
      # - We are merging but the theme (css) does not exist on deploy directory.
      build=0
      if ([[ $(echo "$diff" | grep themes/custom/$theme_type_dir/$theme_dir) ]]); then
        echo -en "Building $theme_dir because there is some change in this folder."
        build=1
      elif ([[ $theme_type_dir == "transac" && $(echo "$diff" | grep themes/custom/$theme_type_dir/alshaya_white_label) ]]); then
        echo -en "Building $theme_dir because the parent theme (alshaya_white_label) has changed."
        build=1
      elif ([[ $theme_type_dir == "non_transac" && $(echo "$diff" | grep themes/custom/$theme_type_dir/whitelabel) ]]); then
        echo -en "Building $theme_dir because the parent theme (whitelabel) has changed."
        build=1
      elif ([[ $theme_type_dir == "amp" && $(echo "$diff" | grep themes/custom/$theme_type_dir/alshaya_amp_white_label) ]]); then
        echo -en "Building $theme_dir because the parent theme (alshaya_amp_white_label) has changed."
        build=1
      elif [ $isGitlab == 0 ]; then
        echo -en "Building $theme_dir because we are outside Travis (or force build is requested)."
        build=1
      elif [ $isGitlabMerge == 1 ]; then
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
        cd $docrootDir/themes/custom/$theme_type_dir/$theme_dir
        npm run build
      else
        # If the theme has not changed are we are on a merge, we copy the css
        # or dist folder from deploy (it contains the source from acquia git).
        if ([ $isGitlabMerge == 1 ])
        then
          if ([ $theme_type_dir == "non_transac" ])
          then
            if [[ $theme_dir == "whitelabel" ]]
            then
              cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/components/dist $docrootDir/themes/custom/$theme_type_dir/$theme_dir/components/
              echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied components/dist folder from deploy directory."
            else
              cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/dist $docrootDir/themes/custom/$theme_type_dir/$theme_dir/
              echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied dist folder from deploy directory."
            fi
          else
            cp -r $docrootDir/../deploy/docroot/themes/custom/$theme_type_dir/$theme_dir/css $docrootDir/themes/custom/$theme_type_dir/$theme_dir/
            echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme. We copied css folder from deploy directory."
          fi
        else
          echo -en "No need to build $theme_dir theme. There is no change in $theme_dir theme."
        fi
      fi

      echo -en "gitlab_fold:end:FE-$theme_dir-Build\r"
    done

  done
else
  echo -en "No need to build any theme. There is no frontend change at all."
fi
