#!/bin/bash
# This file runs post the deploy build is created, and is ready to be committed.

set -e

deployDir="$1"

uname_string=`uname`

# Built css files are ignored in the repository. We need to remove these from
# .gitignore for the css files to be pushed to ACSF.
for dir in $(find $deployDir/docroot/themes/custom -mindepth 1 -maxdepth 1 -type d)
do
  theme_type_dir=${dir##*/}

  if [ $theme_type_dir == 'node_modules' ]
  then
    continue
  fi

  for subdir in $(find $deployDir/docroot/themes/custom/$theme_type_dir -mindepth 1 -maxdepth 1 -type d)
  do
    theme_dir=${subdir##*/}

    if [ $theme_dir == 'node_modules' ]
    then
      continue
    fi

    # Skip directories without gitignore file.
    if [ ! -f $deployDir/docroot/themes/custom/$theme_type_dir/$theme_dir/.gitignore ]; then
      continue
    fi

    if [ $uname_string == 'Darwin' ]
    then
      sed -i'' '/dist/d' $deployDir/docroot/themes/custom/$theme_type_dir/$theme_dir/.gitignore
    else
      sed -i '/dist/d' $deployDir/docroot/themes/custom/$theme_type_dir/$theme_dir/.gitignore
    fi
  done
done

# Delete devel from code base.
if [ -d $deployDir/docroot/modules/contrib/devel ]
then
  rm -Rf $deployDir/docroot/modules/contrib/devel
fi

# Removing `vendor` and `var` directory dynamically from gitignore to push/commit
# to the acquia cloud.
if [ $uname_string == 'Darwin' ]
then
  sed -i'' '/vendor/d' $deployDir/docroot/middleware/.gitignore
  sed -i'' '/var/d' $deployDir/docroot/middleware/.gitignore
  sed -i'' '/vendor/d' $deployDir/docroot/appointment/.gitignore
  sed -i'' '/var/d' $deployDir/docroot/appointment/.gitignore
else
  sed -i '/vendor/d' $deployDir/docroot/middleware/.gitignore
  sed -i '/var/d' $deployDir/docroot/middleware/.gitignore
  sed -i '/vendor/d' $deployDir/docroot/appointment/.gitignore
  sed -i '/var/d' $deployDir/docroot/appointment/.gitignore
fi

# Delete patches directory which is not used on acquia git.
# It can't be done via deploy-exclude-additions.txt given it is needed to
# build the artifact.
rm -Rf $deployDir/patches

# Remove the acsf tools .git file as written in drush/Commands/acsf_tools/README.md
cd $deployDir
git add drush/Commands/acsf_tools
git rm -rf --cached drush/Commands/acsf_tools

cd $deployDir/drush
find 'Commands' -type d -name '.git' -exec rm -rf {} +

# Log the git diff in a file so it can be used later by cloud hooks.
cd $deployDir
git diff --name-only > $deployDir/git-diff.txt
git add $deployDir/git-diff.txt
cd -
