#!/usr/bin/env bash

cd ../../docroot/modules/react

diff=$(git diff .)

if [ ! -z "${diff}" ];
then
    npm test
fi
