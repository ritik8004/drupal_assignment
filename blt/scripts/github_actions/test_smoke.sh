#!/usr/bin/env bash

set -ev

cd ${GITHUB_WORKSPACE}/tests/behat
composer install -n

./behat-build.sh --rebuild=TRUE --site=hm-kw-uat-en

# @todo change below to execute smoke tests on all sites.
bin/behat --profile=hm-kw-uat-en-desktop -c behat-ci.yml --format pretty --tags="@contact-us"

set +v
