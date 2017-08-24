#!/bin/bash
# This file runs during the frontend build.

set -e

docrootDir="$1"

declare -a themes=("whitelabel" "whitelabel_transac" "whitelabel_non_transac" "victoria_secret" "alshaya_white_label")

for i in "${themes[@]}"
do
  cd $docrootDir/themes/custom/$i
  npm run build
done