#!/bin/bash
# This file runs post the deploy build is created, and is ready to be committed.

set -e

deployDir="$1"

# This file is not removed via deploy-exclude-additions.
# Refer https://github.com/acquia/blt/issues/1941
rm $deployDir/docroot/core/install.php

# Built css files are ignored in the repository. We need to remove these from
# .gitignore for the css files to be pushed to ACSF.
themes=( "whitelabel" "whitelabel_transac" "whitelabel_non_transac" "victoria_secret" "alshaya_white_label" "bath_body_works" "debenhams" )

for i in "${themes[@]}"
do
  if [ -f $deployDir/docroot/themes/custom/$i/.gitignore ]
  then
    sed -i '' '/dist/d' $deployDir/docroot/themes/custom/$i/.gitignore
  fi
done
