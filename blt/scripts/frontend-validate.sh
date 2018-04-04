#!/bin/bash
# This file runs during the frontend validate.

set -e

docrootDir="$1"

# TODO: This task seems not defined in non transact sites.
# Also not doing this for amp as of now.
transac=( "alshaya_white_label" "alshaya_hnm" "pottery_barn_non_trans" "alshaya_pottery_barn" "alshaya_victoria_secret" "alshaya_bathbodyworks" )

for i in "${transac[@]}"
do
  cd $docrootDir/themes/custom/transac/$i
  gulp lint:css-with-fail
  gulp lint:js-with-fail
done

exit $?
