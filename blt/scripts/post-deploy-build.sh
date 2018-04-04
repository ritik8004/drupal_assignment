#!/bin/bash
# This file runs post the deploy build is created, and is ready to be committed.

set -e

deployDir="$1"

# Built css files are ignored in the repository. We need to remove these from
# .gitignore for the css files to be pushed to ACSF.
transac=( "alshaya_white_label" "alshaya_hnm" "pottery_barn_non_trans" "alshaya_bathbodyworks" )
non_transac=( "debenhams" "whitelabel" "whitelabel_non_transac" "victoria_secret" "bath_body_works" "bouchon_bakery" )
amp=( "alshaya_amp_white_label" )

for i in "${transac[@]}"
do
  if [ -f $deployDir/docroot/themes/custom/transac/$i/.gitignore ]
  then
    uname_string=`uname`
    if [ $uname_string == 'Darwin' ]
    then
      sed -i '' '/dist/d' $deployDir/docroot/themes/custom/transac/$i/.gitignore
    else
      sed -i '/dist/d' $deployDir/docroot/themes/custom/transac/$i/.gitignore
    fi
  fi
done

for i in "${non_transac[@]}"
do
  if [ -f $deployDir/docroot/themes/custom/non_transac/$i/.gitignore ]
  then
    uname_string=`uname`
    if [ $uname_string == 'Darwin' ]
    then
      sed -i '' '/dist/d' $deployDir/docroot/themes/custom/non_transac/$i/.gitignore
    else
      sed -i '/dist/d' $deployDir/docroot/themes/custom/non_transac/$i/.gitignore
    fi
  fi
done

for i in "${amp[@]}"
do
  if [ -f $deployDir/docroot/themes/custom/amp/$i/.gitignore ]
  then
    uname_string=`uname`
    if [ $uname_string == 'Darwin' ]
    then
      sed -i '' '/dist/d' $deployDir/docroot/themes/custom/amp/$i/.gitignore
    else
      sed -i '/dist/d' $deployDir/docroot/themes/custom/amp/$i/.gitignore
    fi
  fi
done
