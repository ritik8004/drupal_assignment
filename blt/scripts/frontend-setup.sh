#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

themes=( "debenhams" "whitelabel" "whitelabel_transac" "whitelabel_non_transac" "victoria_secret" "alshaya_white_label" "pottery_barn_non_trans" "alshaya_hnm" "bath_body_works" "bouchon_bakery" )

for i in "${themes[@]}"
do
  echo -en "travis_fold:start:FE-Setup-${i}\r"
  cd $docrootDir/themes/custom/$i
  printf "Installing npm in $docrootDir/themes/custom/$i"
  npm run install-tools
  echo -en "travis_fold:end:FE-Setup-${i}\r"
done
