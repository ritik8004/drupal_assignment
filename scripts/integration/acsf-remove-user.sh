#!/bin/sh

# This script is used to remove the ACSF user with the help of email ids.
# Usage: scripts/integration/acsf-remove-user.sh
# Example: scripts/integration/acsf-remove-user.sh test@web.com,test@web.com

# Load the ACSF API credentials.
FILE=$HOME/acsf_api_settings
if [ -f $FILE ]; then
  . $HOME/acsf_api_settings
else
  echo "$HOME/acsf_api_settings does not exist. Please create the file for the script to use ACSF API."
  exit 1
fi

user_emails="$1"

if [ -z "$user_emails" ] ; then
  echo "Argument validation failed, please check and try again"
  exit 1
fi

response=$(curl -sk "https://www.alshaya.acsitefactory.com/api/v1/users?limit=500&fields=mail,uid" -u ${username}:${api_key})
acsf_users=$(php -r '$json = '"'$response'"'; $acsf_users = (array)json_decode($json)->users; echo json_encode($acsf_users);')
for user_email in $(echo $user_emails | tr "," "\n")
do
  user_acsf_uid=`echo $acsf_users | tr -d "[]" | tr "{" '\n' | grep "mail" | grep $user_email | grep -o -E '[0-9]+'`
  if [ -z "$user_acsf_uid" ]; then
    echo "Impossible to find user in ACSF with email: $user_email"
    continue
  else
    response=$(curl --request DELETE "https://www.alshaya.acsitefactory.com/api/v1/users/$user_acsf_uid" -u ${username}:${api_key})
    if [[ "$response" == *'"deleted":true'* ]]; then
      echo "\n User removed from ACSF with email: $user_email"
    else
      echo $response
    fi
  fi
done
