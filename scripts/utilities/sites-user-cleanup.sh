#!/bin/bash
#
# This script cleanup the users from Drupal if user is blocked in ACSF.
#
# ./scripts/utilities/sites-user-cleanup.sh "mckw4,mcsa3,hmkw5,hmae5,pbae3,vsae3,vskw3,bbwae"

sites="$1"
env_suffix=`echo $AH_SITE_ENVIRONMENT | sed -e "s/[0-9]*^*//"`
if [ $env_suffix == "live" ] ; then
  env_suffix=''
  domain_alias=.factory.alshaya.com
else
  domain_alias="-$env_suffix.factory.alshaya.com"
  env_suffix="$env_suffix-"
fi

# Load the ACSF API credentials.
FILE=$HOME/acsf_api_settings
if [ -f $FILE ]; then
  . $HOME/acsf_api_settings
else
  echo "$HOME/acsf_api_settings does not exist. Please create the file for the script to use ACSF API."
  exit 1
fi

echo "Preparing list of sites for cleanup..."
valid_sites=""
for current_site in $(echo $sites | tr "," "\n")
do
  cd /var/www/html/${AH_SITE_NAME}/docroot
  found=$(drush -l $current_site$domain_alias status | grep "DB driver")
  if [ -z "$found" ]; then
    echo "Impossible to find site $current_site on $env_suffix."
    continue
  fi
  valid_sites="$valid_sites,$current_site"
done
echo "Final list of sites: $valid_sites"
echo

# In case no site id has been found, stop here.
if [ -z "$valid_sites" ]; then
  exit
fi

cd /var/www/html/${AH_SITE_NAME}/docroot

# Call ACSF API to get users which are not active.
response=$(curl -sk "https://www.${env_suffix}alshaya.acsitefactory.com/api/v1/users?limit=500&status=blocked" -u ${username}:${api_key})
acsf_users_email=$(php -r '$json = '"'$response'"'; $acsf_users = (array)json_decode($json)->users; echo implode(" ", array_column($acsf_users, "mail"));')

echo
echo "List of blocked users:"
echo $acsf_users_email | tr " " "\n"
echo
echo

# In case no result found, stop here.
if [[ $response =~ "Access denied" ]] || [ -z "$acsf_users_email" ]; then
  echo "Please check the API keys are correct OR no blocked user exists in ACSF."
  exit
fi

read -p "Please confirm details above and say proceed to start the cleanup: " proceed
echo
if [ "$proceed" = "proceed" ]
then
  # Start user/permission cleanup now for each site.
  for current_site in $(echo ${valid_sites:1} | tr "," "\n") ; do
    cancelled_users=""
    echo "Starting process for $current_site"
    for user_email in $acsf_users_email ; do
      username=`drush -l $current_site$domain_alias uinf --mail=$user_email --format=yaml | grep "name: " | cut -c 9-`
      echo "User found with email: $user_email"
      # If we do not find the user, skip checking ahead.
      if [ -z "$username" ]; then
        echo "No user exists with email: $user_email. Processing next user."
        continue
      fi

      echo "Cancelling inactive user with email: $user_email"
      drush -l $current_site$domain_alias user:cancel $username -y && drush -l $current_site$domain_alias user:role:remove administrator $username -y
      echo "User with email: $user_email is removed from site"
      cancelled_users+=" $user_email"
    done
    echo "Finished processing cleanup for $current_site"
    echo
    echo "List of cancelled users:"
    echo $cancelled_users | tr " " "\n"
    echo
  done
else
  echo "Cleanup aborted."
fi
