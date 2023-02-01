#!/bin/bash
#
# This script cleanup the users from Drupal .
#
# ./scripts/utilities/sites-user-cleanup.sh "mckw,mcsa,hmkw,hmae,pbae,vsae,vskw,bbwae"

sites="$1"
env_suffix=`echo $AH_SITE_ENVIRONMENT- | sed -e "s/[0-9]*^*//"`
if env_suffix == 'live' ; then
  env_suffix = ''
fi

# Load the ACSF API credentials.
FILE=$HOME/acsf_api_settings
if [ -f $FILE ]; then
  . $HOME/acsf_api_settings
else
  echo "$HOME/acsf_api_settings does not exist. Please create the file for the script to use ACSF API."
  exit 1
fi

domain_alias=factory.alshaya.com

echo "Preparing list of sites for cleanup..."
valid_sites=""
for current_site in $(echo $sites | tr "," "\n")
do
  cd /var/www/html/${AH_SITE_NAME}/docroot
  found=$(drush -l $current_site.$domain_alias status | grep "DB driver")
  if [ -z "$found" ]; then
    echo "Impossible to find site $current_site on live."
    continue
  fi
  valid_sites="$valid_sites,$current_site"
done
echo "Final list of sites: $valid_sites"
echo

# In case not site id have been found, stop here.
if [ -z "$valid_sites" ]; then
  exit
fi

cd /var/www/html/${AH_SITE_NAME}/docroot

# Call ACSF API to get users which are not active.
response=$(curl -sk "https://www.${env_suffix}alshaya.acsitefactory.com/api/v1/users?limit=500&status=blocked" -u ${username}:${api_key})
acsf_users_email=$(php -r '$json = '"'$response'"'; $acsf_users = (array)json_decode($json)->users; echo implode(" ", array_column($acsf_users, "mail"));')

# In case no result found, stop here.
if [ -z "$acsf_users_email" ]; then
  echo "No blocked user exists in ACSF."
  exit
fi

read -p "Please confirm details above and say proceed to start the cleanup: " proceed
echo
if [ "$proceed" = "proceed" ]
then
  # Start user/permission cleanup now for each site.
  for current_site in $(echo ${valid_sites:1} | tr "," "\n") ; do
    for user_email in $acsf_users_email ; do
      username=`drush -l $current_site.$domain_alias uinf --mail=$user_email --format=yaml | grep "name: " | cut -c 9-`
      # If we do not find the user, skip checking ahead.
      if [ -z "$username" ]; then
        echo "No user exists with email: $user_email. Processing next user."
        continue 1
      fi
      if [ user.status == "blocked" ]; then
        echo "Cancelling inactive user with email: $user_email"
        drush -l $current_site.$domain_alias user:cancel username
        echo "User with email: $user_email is removed from site"
      fi
    done
  done
else
  echo "Cleanup aborted."
fi
