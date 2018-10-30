#!/bin/bash
# This file runs post the deploy build is created, and is ready to be committed.

set -e

deployDir="$1"

# Delete devel from code base.
if [ -d $deployDir/docroot/modules/contrib/devel ]
then
  rm -Rf $deployDir/docroot/modules/contrib/devel
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
