#!/usr/bin/env bash
# This file runs during the frontend build.

set -ev

docrootDir="$1"
diff=""
ignoredDirs=( "dist" "node_modules" )

# Determine if we are on Github Actions.
if [[ $GITHUB_ACTIONS == "true" ]]; then
  # Fetch the last commit from git log.
  log=$(git log -n 1)

  # Pattern for Merge commit.
  re="Merge: ([abcdef0-9]{7,10}) ([abcdef0-9]{7,10})"

  if [[ $log =~ $re ]]; then
    # Get a list of updated files in this merge commit.
    diff=$(git diff ${BASH_REMATCH[1]} ${BASH_REMATCH[2]} --name-only)

    # Looping through the react module folders.
    for subdir in $(find $docrootDir/modules/react -mindepth 1 -maxdepth 1 -type d)
    do
      module_dir=${subdir##*/}

      echo -en "\nStarting build operation for $module_dir module...\n"

      # Ignore some directories which are not react modules for which
      # build is not needed.
      ignore=0
      for ignoredDir in "${ignoredDirs[@]}"
      do
        if ([[ $(echo "$module_dir" | grep $ignoredDir) ]]); then
          ignore=1
        fi
      done

      # Ignore modules not having webpack.config.js file.
      if [ ! -f $docrootDir/modules/react/$module_dir/webpack.config.js ]; then
        echo -e "$docrootDir/modules/react/$module_dir seems not a react module. No webpack.config.js. Not building.\n"
        ignore=1
      fi

      # Skip module building if ignored.
      if ([ $ignore == 1 ]); then
        echo -e "Skipping $module_dir folder.\n"
        continue
      fi

      # We build the module if:
      # - There is a change in common JS directory.
      # - The module has changed.
      # - We are merging but the module (dist) does not exist on deploy directory.
      build=0
      if ([[ $(echo "$diff" | grep modules/react/js) ]]); then
        echo -e "Building $module_dir because there is some change in common (modules/react/js) folder."
        build=1
      elif ([[ $(echo "$diff" | grep modules/react/$module_dir) ]]); then
        echo -e "Building $module_dir because there is some change in this folder."
        build=1
      elif ([ ! -d "$docrootDir/../deploy/docroot/modules/react/$module_dir/dist" ]); then
        echo -e "Building $module_dir because there is no dist folder in $docrootDir/../deploy/docroot/modules/react/$module_dir"
        build=1
      fi

      # We also build the module if:
      # - The react_dependencies.txt exist in the current module,
      # - and there are changes in one of the listed modules.
      # Ignore checking if build is already true.
      dependencyFile="$docrootDir/modules/react/$module_dir/react_dependencies.txt"
      if ([ -f "$dependencyFile" -a $build == 0 ]); then
        IFS=$'\r\n' GLOBIGNORE='*' command eval "dependentModules=($(cat $dependencyFile))"
        for dependentModule in ${dependentModules[@]}
        do
          if ([[ $(echo "$diff" | grep modules/react/$dependentModule) ]]); then
            echo -e "Building $module_dir because there is some change in dependent module $dependentModule.\n"
            build=1
            break
          fi
        done
      fi

      # Finally build the module if change detected.
      if ([ $build == 1 ]); then
        cd $docrootDir/modules/react/$module_dir
        npm run build
      else
        # If the module has not changed, we copy the dist
        # folder from deploy (it contains the source from acquia git).
        cp -r $docrootDir/../deploy/docroot/modules/react/$module_dir/dist $docrootDir/modules/react/$module_dir/
        echo -e "No need to build for $module_dir. There is no change in $module_dir module. We copied dist folder from deploy directory."
      fi
      echo -en "Finished building $module_dir module...\n"
    done
  else
    # Build all modules if could not differentiate changed
    # files in merge commit.
    echo "\nCould not differentiate change in react modules. Building all react modules...\n"
    vendor/bin/blt alshayafe:build-react
  fi
else
  # Build all modules if outside of Github Actions.
  echo "\nOutside of github actions. Building all react modules...\n"
  vendor/bin/blt alshayafe:build-react
fi

