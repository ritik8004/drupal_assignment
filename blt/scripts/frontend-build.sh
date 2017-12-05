#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

themes=( "debenhams" "whitelabel" "whitelabel_transac" "whitelabel_non_transac" "victoria_secret" "alshaya_white_label" "pottery_barn_non_trans" "alshaya_hnm" "bath_body_works" )

for i in "${themes[@]}"
do
  echo -en "travis_fold:start:FE-Build-${i}\r"
  cd $docrootDir/themes/custom/$i
  npm run build
  echo -en "travis_fold:end:FE-Build-${i}\r"
done
