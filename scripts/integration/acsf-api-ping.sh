#!/bin/sh

# Usage: scripts/integration/acsf-api-ping.sh
#
# A simple test if ACSF API is responding properly. Expected answer should be like:
# {"message":"pong","server_time":"2014-11-18T13:44:57+00:00"}

source $(dirname "$0")/includes/global-api-settings.inc.sh

curl 'https://www.alshaya.acsitefactory.com/api/v1/ping' -u $user:$api_key

