#!/bin/bash

scriptDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
docrootDir="$scriptDir/../../docroot"

echo $docrootDir

if [ -d "$docrootDir/proxy" ];
then
  echo "Doing composer install for proxy"
  cd "$docrootDir/proxy"
  composer install
fi

if [ -d "$docrootDir/middleware" ];
then
  echo "Doing composer install for middleware"
  cd "$docrootDir/middleware"
  composer install
fi

if [ -d "$docrootDir/appointment" ];
then
  echo "Doing composer install for appointment"
  cd "$docrootDir/appointment"
  composer install
fi
