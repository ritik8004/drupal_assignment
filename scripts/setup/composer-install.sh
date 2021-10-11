#!/bin/bash

scriptDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
docrootDir="$scriptDir/../..}"

if [ -d "$docrootDir/proxy" ];
then
  cd "$docrootDir/proxy"
  composer install
fi

if [ -d "$docrootDir/middleware" ];
then
  cd "$docrootDir/middleware"
  composer install
fi

if [ -d "$docrootDir/appointment"];
then
  cd "$docrootDir/appointment"
  composer install
fi
