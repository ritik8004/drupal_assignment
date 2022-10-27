#!/usr/bin/env bash

set -ev

cd ${GITHUB_WORKSPACE}/tests/behat
composer install -n

cd bin
npm install
wget --max-redirect=1 https://goo.gl/s4o9Vx -O selenium.jar
java -jar selenium.jar &

cd ..

./behat-build.sh --rebuild=TRUE
# @todo change below to execute smoke tests on all sites.
bin/behat --profile=hm-kw-uat-en-desktop --format pretty --tags="@contact-us"

set +v
