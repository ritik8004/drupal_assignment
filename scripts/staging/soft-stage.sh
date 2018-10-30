#!/bin/bash
#
# This script stage given sites from production to given environment.
#
# soft-stage.sh "mckw,mcsa;hmkw,hmae;pbae;vsae,vskw;bbwae" "01dev3"

# @TODO: Handle both "dev3" and "01dev3" arguments.
# @TODO: Be sure the script works wherever it is called from.
# @TODO: Get the username and api key from an external file.
# @TODO: Integrate with Slack.

username="vincent.bouchet"
api_key="baceaa29994194954c3cd0139c6a332b7603edd1"


sites=$1
target_env="$2"

# Get the environment without the "01" prefix.
env=${target_env:2}

# Move to proper directory to get access to drush9 acsf-tools commands.
#cd /var/www/html/alshaya$target_env/docroot

# Get list of sites to stage.
# Format: "vsae;mckw,mcae,mcsa;hmkw;pbae".
# ";" is to separate stage batches.
# "," is to separate sites in a same batch.
for current_sites in $(echo $sites | tr ";" "\n")
do
  ids=""
  for current_site in $(echo $current_sites | tr "," "\n")
  do
    id=$(php scripts/staging/get-site-id-from-name.php "$current_site")

    if [ $id != 0 ]; then
      ids="$ids,$id"
    fi
  done

  ids=${ids:1}

  # Run ACSF API command to launch staging operation.
  res=$(curl -s "https://www.alshaya.acsitefactory.com/api/v2/stage" \
    -X POST -H "Content-Type: application/json" \
    -d "{\"to_env\": \"${env}\", \"sites\": [ ${ids} ], \"wipe_target_environment\": false, \"synchronize_all_users\": false, \"detailed_status\": false}" \
    -u ${username}:${api_key})

  # Get the task_id from the JSON response.
  task_id=$(echo $res | grep -Eo '"task_id":[0-9]{1,10}') | grep -Eo '[0-9]{1,10}'

  #task_id=119826
  #task_id=119816 #Completed
  #task_id=119806 #Warning
  task_id=119856

  echo $task_id

  # @TODO: This test is not working and always throwing error.
  re='^[0-9]+$'
  if ! [[ $task_id =~ $re ]]; then
    echo "Staging operation for $current_sites failed to start with following message: $res." >&2
    # @TODO: We should not exit and move to next batch.
    exit 1
  fi

  while true ; do
    res=$(curl -s "https://www.alshaya.acsitefactory.com/api/v1/wip/task/${task_id}/status" \
    -u ${username}:${api_key})

    # Break the loop only the task is not "Waiting" anymore.
    if ! [[ $(echo $res | grep "\"status_string\":\"Waiting\"") ]]; then
      break
    fi

    echo "Staging of $current_sites in progress with task id $task_id."
    sleep 60
  done

  if ! [[ $(echo $res | grep "\"status_string\":\"Completed\"") ]]; then
    echo "Staging operation for $current_sites failed with following message: $res." >&2
    # @TODO: We should not exit and move to next batch.
    # @TODO Looks like there is many possible status. Which ones should be considered as failure?
    exit 1
  fi

  # Loop thought the staged sites to:
  # - Take dumps.
  # - Run updb.
  # - Clean commerce data.
  # - Synchronize commerce data.
  for current_site in $(echo $current_sites | tr "," "\n")
  do
    ./reset-individual-site-post-stage.sh "$target_env" "$current_site"
  done


  echo $res

  echo $ids
done
