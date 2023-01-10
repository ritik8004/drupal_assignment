#!/usr/bin/env bash

set -ev

cd ${GITHUB_WORKSPACE}/tests/behat

# @todo change below to execute smoke tests on all sites.
bin/behat --profile=hm-kw-uat-en-desktop -c behat-ci.yml --format pretty --tags="@smoke&&@contact-us&&@guest"

set +v
