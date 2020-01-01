#!/bin/bash

# Load the ACSF API credentials.
FILE=$HOME/acsf_api_settings
if [ -f $FILE ]; then
  . $HOME/acsf_api_settings
else
  echo "$HOME/acsf_api_settings does not exist. Please create the file for the script to use ACSF API."
  exit 1
fi

stage_res=$(curl -s "https://www.alshaya.acsitefactory.com/api/v1/stage" -u ${username}:${api_key})
envs=$(php -r '$json = '"'$stage_res'"'; echo implode(" ", array_keys((array)json_decode($json)->environments));')
for env in $envs ; do
  # Get branch deployed on Stack 1.
  vcs_res=$(curl -sk "https://www.$env-alshaya.acsitefactory.com/api/v1/vcs?type=sites&stack_id=1" -u ${username}:${api_key})
  curr_branch=$(php -r '$json = '"'$vcs_res'"'; echo json_decode($json)->current;')
  echo "$env: $curr_branch"

  # Get branch deployed on Stack 2.
  vcs_res=$(curl -sk "https://www.$env-alshaya.acsitefactory.com/api/v1/vcs?type=sites&stack_id=2" -u ${username}:${api_key})
  curr_branch=$(php -r '$json = '"'$vcs_res'"'; echo json_decode($json)->current;')
  echo "$env: $curr_branch"
done
