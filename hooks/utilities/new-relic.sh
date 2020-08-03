#!/bin/bash

target_env="$1"
code_change_type="$2" # deploy / update
branch_tag="$3"

# We don't do anything if there is no setting file with the API key.
# This file will be present only on 02pprod and 02live environments.
FILE=$HOME/newrelic_settings
if [ -f $FILE ]; then
  . $HOME/newrelic_settings
else
  echo "${HOME}/newrelic_settings file does not exist."
  exit 0
fi

# We get all the apps in NewRelic account and we determine the one which
# are impacted by the deployment by checking the environment name in the
# application name (pattern on NR is alshaya.<environment>.<sitename>).
# @see factory-hooks/post-settings-php/new-relic.php
nr_apps=$(curl -X GET -sk "https://api.newrelic.com/v2/applications.json" -H "X-Api-Key:${nr_api_key}")
nr_app_ids=$(php -r '$pattern="alshaya.'"$target_env"'"; $json = '"'$nr_apps'"'; $applications=json_decode($json)->applications; $ids=[]; foreach ($applications as $application) { if (substr($application->name, 0, strlen($pattern)) == $pattern) { $ids[] = $application->id; } } echo implode(" ", $ids);')

# We prepare a description text based on the branch/tag and deployment type.
tag=${branch_tag:0:4}
description="Branch ${branch_tag} updated"
if [ $code_change_type == "deploy" -a $tag == "tags" ]; then
  description="Tag ${branch_tag:5} deployed"
elif [ $code_change_type == "deploy" ]; then
  description="Branch ${branch_tag} deployed"
fi

# For each identified application impacted by the deployment, we add a marker
# in the timeline.
for nr_app_id in $nr_app_ids ; do
  curl -X POST "https://api.newrelic.com/v2/applications/${nr_app_id}/deployments.json" \
    -H "X-Api-Key:${nr_api_key}" -i \
    -H 'Content-Type: application/json' \
    -d \
'{
 "deployment": {
   "revision": "'"${branch_tag}"'",
   "description": "'"${description}"'"
 }
}'
done
