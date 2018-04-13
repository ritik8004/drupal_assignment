#!/bin/sh

# Usage: scripts/integration/get-task-status.sh
#
# Returns status of a specified task.
#
# Mandatory parameters:
# task_id : id of task to get information to. This can be found from response e.g. from deploy-staging-environment.shExample: 59951
#
# Important (for later purposes) is "status_string" in response. This is "Waiting" when in progress
# and "Completed" when successfully completed

source $(dirname "$0")/includes/global-api-settings.inc.sh

task_id="$1"

curl "https://www.alshaya.acsitefactory.com/api/v1/wip/task/${task_id}/status" -u $user:$api_key

