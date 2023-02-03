#!/bin/bash
#
# This script remove team member from ACSF,ACE and Drupal for given users.
#
# ./scripts/utilities/prod_remove_team_member.sh "mckw4,mcsa3,hmkw5,hmae5,pbae3,vsae3,vskw3,bbwae" "test1@acquia.com,test2@acquia.com"

sites="$1"
user_emails="$2"

env=`echo $AH_SITE_ENVIRONMENT | sed -e "s/[0-9]*^*//"`
if [ $env != "live" ]
then
  echo "This script is used to execute on prod environments :)"
  exit
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
domain_alias=factory.alshaya.com
blt_dir="${server_root}/vendor/acquia/blt"
server_root="/var/www/html/$AH_SITE_NAME"
acsf_remove_user="${server_root}/scripts/integration/acsf-remove-user.sh"

read -p "Please confirm details above and say proceed to start the cleanup: " proceed
echo
if [ "$proceed" = "proceed" ]
then
  # Start user/permission cleanup now for each site.
  for current_site in $(echo ${valid_sites:1} | tr "," "\n") ; do
    echo "Starting user cancellation and removing administrator role from site: $current_site"
    # Get the user names from which are site administrator from site.
    usernames=`drush -l $current_site.$domain_alias uinf --mail=$user_emails --format=yaml | grep "name: " | cut -c 9-`
    cancelled_users=""
    echo "Starting process for $current_site"
    for username in $usernames ; do
      echo "Cancelling user with email: $user_email"
      drush -l $current_site.$domain_alias user:cancel $username -y && drush -l $current_site.$domain_alias user:role:remove administrator $username -y
      echo "User with email: $user_email is cancelled from site"
      cancelled_users+=" $user_email"
    done
    echo "Finished processing cleanup for $current_site"
    echo
    echo "List of cancelled users:"
    echo $cancelled_users | tr " " "\n"
    echo
  done
  # Start removing users from ACSF.
  echo "Removing bocked user from ACSF console for given emails."
  sh $acsf_remove_user $user_emails

  # Start removing user from Acquia cloud.
  echo "Removing bocked user from Acquia cloud for given emails."
  $blt_dir/bin/blt acquia-cloud-remove-member $user_emails
else
  echo "Cleanup aborted."
fi
