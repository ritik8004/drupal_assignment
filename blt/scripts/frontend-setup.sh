#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

declare -a themes=("whitelabel" "whitelabel_transac" "whitelabel_non_transac" "victoria_secret" "alshaya_white_label")
for i in "${themes[@]}"
do
  cd $docrootDir/themes/custom/$i
  if [ ! -d "node_modules" ]; then
    printf "Installing npm in $docrootDir/themes/custom/$i"
    npm install
  fi

  printf "Installing frontend tools in $docrootDir/themes/custom/$i"
  npm run install-tools
done
