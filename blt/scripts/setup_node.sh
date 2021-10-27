#!/bin/bash

# This file runs during the install phase in travis.

nodeVersion=$(node --version)
if [ "$nodeVersion" != "v8.12.0" ]
then
  echo "Installing node using nvm to match the expected version."
  nvm install 8.12.0
  nvm use 8.12.0
else
  echo "Not installing node again as version matches what we need."
fi

npmVersion=$(npm --version)
if [ "$npmVersion" != "6.13.4" ]
then
  echo "Installing npm to match the expected version."
  npm i -g npm@6.13.4
else
  echo "Not installing NPM again as version matches what we need."
  npm rebuild
fi

gulpExists=$(which gulp)
if [ "$gulpExists" = "" ]
then
  echo "Installing gulp as not available."
  npm install -g gulp-cli
else
  echo "Not installing gulp as it is already available."
fi
