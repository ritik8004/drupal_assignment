#!/usr/bin/env bash

set -ev

cd ${GITHUB_WORKSPACE}/tests/behat
composer install -n

cd bin
npm install

cd ..

java -Dwebdriver.chrome.driver=bin/node_modules/chromedriver/bin/chromedriver -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar &
sleep 3

./behat-build.sh --rebuild=TRUE --site=hm-kw-uat-en

# @todo change below to execute smoke tests on all sites.
bin/behat --profile=hm-kw-uat-en-desktop --format pretty --tags="@contact-us"

set +v
