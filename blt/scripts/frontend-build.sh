#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

transac=( "alshaya_white_label" "alshaya_hnm" "pottery_barn_non_trans" "alshaya_pottery_barn" "alshaya_victoria_secret" "alshaya_bathbodyworks" )
non_transac=( "debenhams" "whitelabel" "whitelabel_non_transac" "victoria_secret" "bath_body_works" "bouchon_bakery" )
amp=( "alshaya_amp_white_label" "alshaya_amp_hnm" "alshaya_amp_victoria_secret")

for i in "${transac[@]}"
do
  echo -en "travis_fold:start:FE-Build-${i}\r"
  cd $docrootDir/themes/custom/transac/$i
  npm run build
  echo -en "travis_fold:end:FE-Build-${i}\r"
done

for i in "${non_transac[@]}"
do
  echo -en "travis_fold:start:FE-Build-${i}\r"
  cd $docrootDir/themes/custom/non_transac/$i
  npm run build
  echo -en "travis_fold:end:FE-Build-${i}\r"
done

for i in "${amp[@]}"
do
  echo -en "travis_fold:start:FE-Build-${i}\r"
  cd $docrootDir/themes/custom/amp/$i
  npm run build
  echo -en "travis_fold:end:FE-Build-${i}\r"
done
