
#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

isGitlab=0
isGitlabPr=0
isGitlabMerge=0
diff=""

# Determine if we are on Travis.
if [[ $CI && $CI == "true" ]]; then
  isGitlab=1

  if [[ $CI_PROJECT_NAME && $CI_PROJECT_NAME == "alshaya-pso" ]]; then
    isGitlabMerge=1
  else
    isGitlabPr=1
    git fetch origin $CI_COMMIT_BRANCH:$CI_COMMIT_BRANCH-frontend-check
    diff=$(git diff --name-only $CI_COMMIT_BRANCH-frontend-check)
  fi
fi

# We only setup if we are not on Travis or if it is changed.
echo -en "gitlab_fold:start:REACT-Setup\r"

# We build if:
  # - We are outside Travis context.
  # - Modules have changed.
  # - We are merging but the dist dir does not exist on deploy directory.
setup=0
if [ $isGitlabMerge == 1 ]; then
  echo -en "Setup REACT because we are merging a PR."
  setup=1
elif [ $isGitlab == 0 ]; then
  echo -en "Setup REACT because it is outside Travis."
  setup=1
elif ([[ $(echo "$diff" | grep modules/react) ]]); then
  echo -en "Setup REACT because there are changes in this folder."
  setup=1
fi

if ([ $setup == 1 ])
then
  cd $docrootDir/modules/react
  npm install

  ignoredDirs=( "node_modules" "alshaya_react" "js" "dist" )

  # Validate utility files.
  npm run lint $docrootDir/modules/react/js/

  # Validate files now.
  for subdir in $(find $docrootDir/modules/react -mindepth 1 -maxdepth 1 -type d -not -path '*/\.*')
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
else
  echo -en "No need to setup REACT. There is no change in any modules/react."
fi

echo -en "travis_fold:end:REACT-Setup\r"
