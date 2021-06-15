#!/usr/bin/env bash

cd ../../docroot/modules/react
diff=$(git diff --name-only . | grep ".js")
if [ ! -z "${diff}" ];
then
  npm test
fi
