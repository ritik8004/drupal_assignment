#!/usr/bin/env bash

set -ev

cd ${GITHUB_WORKSPACE}/tests/behat

composer install -n
chmod -R a+rx vendor
./behat-build.sh --rebuild=TRUE --site=hm-kw-uat-en

#java -Dwebdriver.chrome.driver=${CHROMEWEBDRIVER} -jar ${SELENIUM_JAR_PATH} standalone --port 4444 &

set +v
