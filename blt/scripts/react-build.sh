#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

isTravis=0
isTravisPr=0
isTravisMerge=0
diff=""

ignoredDirs=( "node_modules" )

# Determine if we are on Travis.
if [[ $TRAVIS && $TRAVIS == "true" ]]; then
  isTravis=1

  if [[ $TRAVIS_PULL_REQUEST && $TRAVIS_PULL_REQUEST == "false" ]]; then
    isTravisMerge=1
    log=$(git log -n 1)

    # Extract commit IDs from git log.
    re="Merge: ([abcdef0-9]{7,8}) ([abcdef0-9]{7,8})"

    if [[ $log =~ $re ]]; then
      # Get a list of updated files in this PR.
      diff=$(git diff ${BASH_REMATCH[1]} ${BASH_REMATCH[2]} --name-only)
    else
      isTravis=0
      echo "Not able to identify commit IDs to do the diff. Building all the modules."
    fi

    # If the PR title or merge contains "FORCE" we will build all modules.
    if ([[ $(echo "$log" | grep "FORCE") ]])
    then
      isTravis=0
    fi
  else
    isTravisPr=1
    diff=$(git diff --name-only $TRAVIS_BRANCH-frontend-check)
  fi
fi

# We always build react unless we are testing a simple push on Travis and there
# is no change in react.
if ([ $isTravis == 0 ]) || ([ $isTravisMerge == 1 ]) || ([[ $(echo "$diff" | grep /react/) ]])
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

    echo -e "travis_fold:start:REACT-$module_dir-Build\r"

    # We build the module if:
    # - There is a change in common JS directory.
    # - We are outside Travis context.
    # - The module has changed.
    # - We are merging but the module (dist) does not exist on deploy directory.
    build=0
    if ([[ $(echo "$diff" | grep modules/react/js) ]]); then
      echo -e "Building $module_dir because there is some change in common (modules/react/js) folder."
      build=1
    elif ([[ $(echo "$diff" | grep modules/react/$module_dir) ]]); then
      echo -e "Building $module_dir because there is some change in this folder."
      build=1
    elif [ $isTravis == 0 ]; then
      echo -e "Building $module_dir because we are outside Travis (or force build is requested)."
      build=1
    elif [ $isTravisMerge == 1 ]; then
      if ([ ! -d "$docrootDir/../deploy/docroot/modules/react/$module_dir/dist" ])
      then
        echo -e "Building $module_dir because there is no dist folder in $docrootDir/../deploy/docroot/modules/react/$module_dir"
        build=1
      fi
    fi

    # We build the module if:
    # - The react_dependencies.txt exist in the current module,
    # - and there are changes in one of the listed modules.
    # Ignore checking if build is already true.
    dependecyFile="$docrootDir/modules/react/$module_dir/react_dependencies.txt"
    if ([ -f "$dependecyFile" -a $build == 0 ])
    then
      IFS=$'\r\n' GLOBIGNORE='*' command eval "dependentModules=($(cat $dependecyFile))"
      for dependentModule in ${dependentModules[@]}
      do
        if ([[ $(echo "$diff" | grep modules/react/$dependentModule) ]]); then
          echo -e "Building $module_dir because there is some change in dependent module $dependentModule."
          build=1
          break
        fi
      done
    fi

    if ([ $build == 1 ])
    then
      cd $docrootDir/modules/react/$module_dir
      npm run build
    else
      # If the module has not changed and we are on a merge, we copy the dist
      # folder from deploy (it contains the source from acquia git).
      if ([ $isTravisMerge == 1 ])
      then
        cp -r $docrootDir/../deploy/docroot/modules/react/$module_dir/dist $docrootDir/modules/react/$module_dir/
        echo -e "No need to build for $module_dir. There is no change in $module_dir module. We copied dist folder from deploy directory."
      else
        echo -e "No need to build for $module_dir. There is no change in $module_dir module."
      fi
    fi

    echo -e "travis_fold:end:REACT-$module_dir-Build\r"
  done

else
  echo -e "No need to build REACT. There is no change at all."
fi

# Empty line to ensure we can see full output.
echo -e ""
