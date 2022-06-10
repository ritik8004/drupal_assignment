#!/bin/bash

# Load the Deployed branches using Heroku proxy if available.
if [ ! "$acsf_deployed_branches_proxy" = "" ]
then
  deployed_branches=$(curl -sk "$acsf_deployed_branches_proxy" --header 'ALSHAYAREQUEST: 1' --max-time 30)
  echo "$deployed_branches"
  exit 0
fi

# Load the ACSF API credentials.
FILE=$HOME/acsf_api_settings
if [ -f $FILE ]; then
  . $HOME/acsf_api_settings
else
  echo "$HOME/acsf_api_settings does not exist. Please create the file for the script to use ACSF API."
  exit 1
fi

# Get the list of environments using the API.
stage_res=$(curl -sk "https://www.alshaya.acsitefactory.com/api/v1/stage" -u ${username}:${api_key} --max-time 60)
envs=$(php -r '$json = '"'$stage_res'"'; echo implode(" ", array_keys((array)json_decode($json)->environments));')

# Get the list of stacks using the API. Exclude the "Alshaya DC 1" stack.
stacks_res=$(curl -sk "https://www.alshaya.acsitefactory.com/api/v1/stacks" -u ${username}:${api_key} --max-time 60)
stacks=$(php -r '$json = '"'$stacks_res'"'; $stacks = (array)json_decode($json)->stacks; if (isset($stacks[5]) && $stacks[5] === "Alshaya DC 1") { unset($stacks[5]); } echo implode(" ", array_keys($stacks));')

# For each env+stack combination, print the deployed branch.
for env in $envs ; do
  for stack in $stacks ; do
    vcs_res=$(curl -sk "https://www.$env-alshaya.acsitefactory.com/api/v1/vcs?type=sites&stack_id=$stack" -u ${username}:${api_key} --max-time 30)
    curr_branch=$(php -r '$json = '"'$vcs_res'"'; $decoded = json_decode($json); echo isset($decoded->current) ? $decoded->current : "error" ;')

    if [ "$curr_branch" = "error" ]; then
      echo "Impossible to fetch the name of the deployed for env $env on stack id $stack."
      exit 1
    fi

    echo "Stack $stack - Env $env: $curr_branch"
  done
done

exit 0
