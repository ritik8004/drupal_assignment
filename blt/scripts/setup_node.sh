#!/bin/bash

# This file runs during the install phase in travis.

nvm install 8.12.0
nvm use 8.12.0
npm i -g npm@6.13.4
npm install -g gulp-cli
