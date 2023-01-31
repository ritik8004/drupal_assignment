#!/bin/bash
#
# This script cleanup the users from Drupal .
#
# ./scripts/utilities/drupal-user-cleanup.sh "mckw,mcsa,hmkw,hmae,pbae,vsae,vskw,bbwae"

sites="$1"

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
response=$(curl -sk "https://www.alshaya.acsitefactory.com/api/v1/users?limit=500&status=blocked" -u ${username}:${api_key})
acsf_users=$(php -r '$json = '"'$response'"'; echo (array)json_decode($json)->users;')

# In case no result found, stop here.
if [ -z "$acsf_users" ]; then
  exit
fi
# Start user/permission cleanup now for each site.
for current_site in $(echo ${valid_sites:1} | tr "," "\n") ; do
  for user in acsf_users ; do
    user_email=$user.mail
    username=`drush -l $current_site.$source_domain uinf --mail=$user_email --format=yaml | grep "name: " | cut -c 9-`
    # If we do not find the user, skip checking ahead.
    if [ -z "$username" ]; then
      echo "No user exists with email: $user_email. Processing next user."
      continue 1
    fi
    if [ user.status == "blocked" ]; then
      echo "Cancelling inactive user with email: $user_email"
      drush -l $current_site.$source_domain user:cancel username
      echo "User with email: $user_email is removed from site"
    fi
  done
done
