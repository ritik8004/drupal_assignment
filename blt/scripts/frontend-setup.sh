
#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

isGitlab=0
isGitlabPr=0
isGitlabMerge=0
diff=""

echo "CI: $CI"
echo "CI BUILDS DIR: $CI_BUILDS_DIR"
echo "CI COMMIT BEFORE SHA: $CI_COMMIT_BEFORE_SHA"
echo "CI COMMIT DESCRIPTION: $CI_COMMIT_DESCRIPTION"
echo "CI COMMIT REF NAME: $CI_COMMIT_REF_NAME"
echo "CI COMMIT REF PROTECTED: $CI_COMMIT_REF_PROTECTED"
echo "CI COMMIT REF SLUG: $CI_COMMIT_REF_SLUG"
echo "CI COMMIT SHA: $CI_COMMIT_SHA"
echo "CI COMMIT SHORT SHA: $CI_COMMIT_SHORT_SHA"
echo "CI COMMIT BRANCH: $CI_COMMIT_BRANCH"
echo "CI_COMMIT_TAG: $CI_COMMIT_TAG"
echo "CI_COMMIT_TITLE: $CI_COMMIT_TITLE"
echo "CI DEFAULT BRANCH: $CI_DEFAULT_BRANCH"
echo "CI ENVIRONMENT NAME: $CI_ENVIRONMENT_NAME"
echo "CI EXTERNAL PULL REQUEST IID: $CI_EXTERNAL_PULL_REQUEST_IID"
echo "CI EXTERNAL PULL REQUEST SOURCE REPOSITORY: $CI_EXTERNAL_PULL_REQUEST_SOURCE_REPOSITORY"
echo "CI EXTERNAL PULL REQUEST TARGET REPOSITORY: $CI_EXTERNAL_PULL_REQUEST_TARGET_REPOSITORY"
echo "CI EXTERNAL PULL REQUEST SOURCE BRANCH NAME: $CI_EXTERNAL_PULL_REQUEST_SOURCE_BRANCH_NAME"
echo "CI EXTERNAL PULL REQUEST SOURCE BRANCH SHA: $CI_EXTERNAL_PULL_REQUEST_SOURCE_BRANCH_SHA"
echo "CI EXTERNAL PULL REQUEST TARGET BRANCH NAME: $CI_EXTERNAL_PULL_REQUEST_TARGET_BRANCH_NAME"
echo "CI EXTERNAL PULL REQUEST TARGET BRANCH SHA: $CI_EXTERNAL_PULL_REQUEST_TARGET_BRANCH_SHA"
echo "CI PROJECT TITLE: $CI_PROJECT_TITLE"
echo "CI MERGE REQUEST TITLE: $CI_MERGE_REQUEST_TITLE"
echo "CI MERGE REQUEST EVENT TYPE: $CI_MERGE_REQUEST_EVENT_TYPE"
echo "CI MERGE REQUEST DIFF ID: $CI_MERGE_REQUEST_DIFF_ID"
echo "CI MERGE REQUEST DIFF BASE SHA: $CI_MERGE_REQUEST_DIFF_BASE_SHA"
echo "CI MERGE REQUEST TITLE: $CI_MERGE_REQUEST_TITLE"
echo "GITLAB CI: $GITLAB_CI"

echo "CI_COMMIT_MESSAGE: $CI_COMMIT_MESSAGE"
echo "CI_MERGE_REQUEST: $CI_MERGE_REQUEST_ID"
echo ""

# Determine if we are on Gitlab.
if [[ $CI && $GITLAB == "true" ]]; then
  isGitlab=1

  if [[ $CI_MERGE_REQUEST && $CI_MERGE_REQUEST == "false" ]]; then
    isGitlabMerge=1
  else
    isGitlabPr=1
    git fetch origin $CI_MERGE_REQUEST_TARGET_BRANCH_NAME:$CI_MERGE_REQUEST_TARGET_BRANCH_NAME-frontend-check
    diff=$(git diff --name-only $CI_MERGE_REQUEST_TARGET_BRANCH_NAME-frontend-check)
  fi
fi

# We only setup themes on if we are not on Gitlab or if themes have changed.
for dir in $(find $docrootDir/themes/custom -mindepth 1 -maxdepth 1 -type d)
do
  theme_type_dir=${dir##*/}

  echo -en "gitlab_fold:start:FE-$theme_type_dir-Setup\r"

  # We build the theme if:
    # - We are outside Gitlab context.
    # - The theme has changed.
    # - We are merging but the theme (css) does not exist on deploy directory.
  setup=0
  if [ $isGitlabMerge == 1 ]; then
    echo -en "Setup $theme_type_dir because we are merging a PR."
    setup=1
  elif [ $isGitlab == 0 ]; then
    echo -en "Setup $theme_type_dir because it is outside Gitlab."
    setup=1
  elif ([[ $(echo "$diff" | grep themes/custom/$theme_type_dir) ]]); then
    echo -en "Setup $theme_type_dir because there is some change in this folder."
    setup=1
  fi

  if ([ $setup == 1 ])
  then
    cd $docrootDir/themes/custom/$theme_type_dir
    npm run install-tools

    # TODO: Increase test coverage to all the themes.
    # Validate only for gitlab PRs.
    if [[ $isGitlabPr == 1 && $theme_type_dir == 'transac' ]]
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
  else
    echo -en "No need to setup $theme_type_dir frontend. There is no change in any $theme_type_dir themes."
  fi

  echo -en "gitlab_fold:end:FE-$theme_type_dir-Setup\r"
done
