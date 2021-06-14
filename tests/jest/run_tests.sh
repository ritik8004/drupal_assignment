#!/usr/bin/env bash

cd ../../docroot/modules/react

if [ -d "node_modules/jest" ];
then
    npm test
fi
