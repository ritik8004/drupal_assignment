#!/bin/sh

# Usage: scripts/integration/acsf-api-ping-non-prod.sh
#
# A simple test if ACSF API on non-prod environment is responding properly.
# Non-prod environment has -k parameter mandatory
# Expected answer should be like:
# {"message":"pong","server_time":"2014-11-18T13:44:57+00:00"}
#
#
#
# Mandatory parameters:
# env : environment to ping. Example: dev, pprod, qa2, test.

source $(dirname "$0")/includes/global-api-settings.inc.sh
env="$1"

echo "Pinging env ${env}"
curl "https://www.${env}-alshaya.acsitefactory.com/api/v1/ping" -u $user:$api_key -k

