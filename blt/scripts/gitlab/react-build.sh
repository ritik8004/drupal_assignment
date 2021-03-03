#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

isGitlab=0
isGitlabPr=0
isGitlabMerge=0
diff=""

ignoredDirs=( "node_modules" )

# Determine if we are on Gitlab.
if [[ $CI && $CI == "true" ]]; then
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
      echo "Not able to identify commit IDs to do the diff. Building all the modules."
    fi

    # If the PR title or merge contains "FORCE" we will build all modules.
    if ([[ $(echo "$log" | grep "FORCE") ]])
    then
      isGitlab=0
    fi
  else
    isGitlabPr=1
    diff=$(git diff --name-only $CI_MERGE_REQUEST_TARGET_BRANCH_NAME-frontend-check)
  fi
fi

# We always build react unless we are testing a simple push on Gitlab and there
# is no change in react.
if ([ $isGitlab == 0 ]) || ([ $isGitlabMerge == 1 ]) || ([[ $(echo "$diff" | grep /react/) ]])
then

  for subdir in $(find $docrootDir/modules/react -mindepth 1 -maxdepth 1 -type d)
  do
    module_dir=${subdir##*/}

    # Ignore some directories which are not modules (node_modules) or which
    # don't need to be build (alshaya_react).
    ignore=0
    for ignoredDir in "${ignoredDirs[@]}"
    do
      if ([[ $(echo "$module_dir" | grep $ignoredDir) ]])
      then
        ignore=1
      fi
    done

    if [ ! -f $docrootDir/modules/react/$module_dir/webpack.config.js ]; then
      echo -e "$module_dir seems not a valid module. No webpack.config.js. Not building."
      ignore=1
    fi

    if ([ $ignore == 1 ])
    then
      continue
    fi

    echo -e "gitlab_fold:start:REACT-$module_dir-Build\r"

    # We build the module if:
    # - We are outside Gitlab context.
    # - The module has changed.
    # - We are merging but the module (dist) does not exist on deploy directory.
    build=0
    if ([[ $(echo "$diff" | grep modules/react/$module_dir) ]]); then
      echo -e "Building $module_dir because there is some change in this folder."
      build=1
    elif [ $isGitlab == 0 ]; then
      echo -e "Building $module_dir because we are outside Gitlab (or force build is requested)."
      build=1
    elif [ $isGitlabMerge == 1 ]; then
      if ([ ! -d "$docrootDir/../deploy/docroot/modules/react/$module_dir/dist" ])
      then
        echo -e "Building $module_dir because there is no dist folder in $docrootDir/../deploy/docroot/modules/react/$module_dir"
        build=1
      fi
    fi

    if ([ $build == 1 ])
    then
      cd $docrootDir/modules/react/$module_dir
      npm run build
    else
      # If the module has not changed and we are on a merge, we copy the dist
      # folder from deploy (it contains the source from acquia git).
      if ([ $isGitlabMerge == 1 ])
      then
        cp -r $docrootDir/../deploy/docroot/modules/react/$module_dir/dist $docrootDir/modules/react/$module_dir/
        echo -e "No need to build for $module_dir. There is no change in $module_dir module. We copied dist folder from deploy directory."
      else
        echo -e "No need to build for $module_dir. There is no change in $module_dir module."
      fi
    fi

    echo -e "gitlab_fold:end:REACT-$module_dir-Build\r"
  done

else
  echo -e "No need to build REACT. There is no change at all."
fi

# Empty line to ensure we can see full output.
echo -e ""
