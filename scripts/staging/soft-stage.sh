#!/bin/bash
#
# This script soft stage given sites from production to given environment
# and execute post-stage operation on these.
#
# soft-stage.sh "mckw,mcsa;hmkw,hmae;pbae;vsae,vskw;bbwae" "01dev3"
#
# @TODO: Make it possible to run from local.

sites=$1
target_env="$2"

# Normalize the target environment.
if [ ${target_env:0:2} != "01" ]; then
  target_env="01$target_env"
fi

env=${target_env:2}

# Load the ACSF API credentials.
FILE=$HOME/acsf_api_settings
if [ -f $FILE ]; then
  . $HOME/acsf_api_settings
else
  echo "$HOME/acsf_api_settings does not exist. Please create the file for the script to use ACSF API."
  exit 1
fi

# Load the Slack settings.
FILE=$HOME/slack_settings
if [ -f $FILE ]; then
  . $HOME/slack_settings
else
  echo "$HOME/slack_settings does not exist. Please configure Slack integration for the script to notify Slack channel."
  exit 1
fi

# Move to proper directory to get access to drush9 acsf-tools commands.
cd /var/www/html/alshaya$target_env/docroot

# Get list of sites to stage.
# Format: "vsae;mckw,mcae,mcsa;hmkw;pbae".
# ";" is to separate site batches.
# "," is to separate sites in a same batch.
for current_sites in $(echo $sites | tr ";" "\n")
do
  ids=""
  valid_sites=""

  for current_site in $(echo $current_sites | tr "," "\n")
  do
    res=$(curl -s "https://www.alshaya.acsitefactory.com/api/v1/sites?limit=500" \
      -u ${username}:${api_key})

    # Finding the site id from its name require to browse a JSON which is
    # complex in patch. Invoking php script for this is simpler.
    id=$(php ../scripts/staging/sub-php/get-site-id-from-name.php "$res" "$current_site")

    if [ $id != 0 ]; then
      ids="$ids,$id"
      valid_sites="$valid_sites,$current_site"
    else
      echo "Impossible to find site id for $current_site."
    fi
  done

  # In case not site id have been found, move to next batch.
  if [[ $ids == "" ]]; then
    continue
  fi

  # Remove first comma from strings.
  ids=${ids:1}
  current_sites=${valid_sites:1}

  # Notify Slack of the operation starting.
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Starting soft-stage of $current_sites on $target_env.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null

  # Run ACSF API command to launch staging operation.
  res=$(curl -s "https://www.alshaya.acsitefactory.com/api/v2/stage" \
    -X POST -H "Content-Type: application/json" \
    -d "{\"to_env\": \"${env}\", \"sites\": [ ${ids} ], \"wipe_target_environment\": false, \"synchronize_all_users\": false, \"detailed_status\": false}" \
    -u ${username}:${api_key})

  # Get the task_id from the JSON response.
  task_id=$(echo $(echo $res | grep -Eo '"task_id":[0-9]{1,10}') | grep -Eo '[0-9]{1,10}')

  # Check if the staging operation started properly.
  re='^[0-9]+$'
  if ! [[ $task_id =~ $re ]]; then
    echo "Staging operation for $current_sites failed to start with following message: $res."
    continue
  fi

  # Wait for the staging operation to complete.
  loop=0
  while true ; do
    res=$(curl -s "https://www.alshaya.acsitefactory.com/api/v1/wip/task/${task_id}/status" \
    -u ${username}:${api_key})

    # Break the loop only the task is not "Waiting", "Not Started", "In Progress" anymore.
    if ! [[ $(echo $res | grep -E "\"status_string\":\"Waiting\"|\"status_string\":\"Not Started\"|\"status_string\":\"In Progress\"") ]]; then
      echo "Staging operation for $current_sites is completed."
      break
    fi

    echo "Staging of $current_sites in progress with task id $task_id."

    # Notify Slack of the operation progress.
    loop=$((loop+1))
    if [[ $loop == 5 ]]; then
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Soft-stage of $current_sites on $target_env still in progress.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
      loop=0
    fi

    sleep 60
  done

  # @TODO Looks like there is many possible status. Which ones should be considered as failure?
  if ! [[ $(echo $res | grep "\"status_string\":\"Completed\"") ]]; then
    echo "Staging operation for $current_sites failed with following message: $res."
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Staging operation of $current_sites on $target_env failed with following message: $res.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null
    continue
  fi

  echo "Staging operation of $current_sites on $target_env finished successfully, now starting post-stage operations."
  curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Staging operation of $current_sites on $target_env finished successfully, now starting post-stage operations.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL -s > /dev/null

  # Loop through the staged sites to:
  # - Take dumps.
  # - Run updb.
  # - Clean commerce data.
  # - Synchronize commerce data.
  # - Take dumps.
  for current_site in $(echo $current_sites | tr "," "\n")
  do
    ./../scripts/staging/reset-individual-site-post-stage.sh "$target_env" "$current_site"
  done

done
