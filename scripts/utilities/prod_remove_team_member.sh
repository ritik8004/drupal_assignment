#!/bin/bash
#
# This script remove team member from ACSF,ACE and Drupal for given users.
#
# ./scripts/utilities/prod_remove_team_member.sh "test1@acquia.com,test2@acquia.com"

env=`echo $AH_SITE_ENVIRONMENT | sed -e "s/[0-9]*^*//"`
if [ $env != "live" ]
then
  echo "This script is used to execute on prod environments :)"
  exit
fi

user_emails="$1"

if [ -z "$user_emails" ] ; then
  echo "Argument validation failed, please check and try again"
  exit
fi

domain_alias=factory.alshaya.com
server_root="/var/www/html/$AH_SITE_NAME"
blt_dir="${server_root}/vendor/acquia/blt"

cd /var/www/html/${AH_SITE_NAME}/docroot

echo "Preparing list of sites for cleanup..."
sites=`drush acsf-tools-list | grep -v " "`

echo "============================================"
echo "Sites for cleanup:"
echo $sites
echo
echo "Following users will be cancelled from sites and removed from ACSF and Acquia cloud"
echo $user_emails
echo "============================================"

acsf_remove_user="${server_root}/scripts/integration/acsf-remove-user.sh"

read -p "Please confirm details above and type proceed to start the cleanup: " proceed
echo
if [ "$proceed" = "proceed" ]
then
  # Start user/permission cleanup now for each site.
  for current_site in $sites ; do
    echo "Starting user cancellation and removing administrator role from site: $current_site"
    # Get the user names from which are site administrator from site.
    usernames=`drush -l $current_site.$domain_alias uinf --mail=$user_emails --format=yaml | grep "name: " | cut -c 9-`
    cancelled_users=""
    echo "Starting process for $current_site"
    for username in $usernames ; do
      echo "Cancelling user with user name: $username"
      drush -l $current_site.$domain_alias user:cancel $username -y && drush -l $current_site.$domain_alias user:role:remove administrator $username -y
      echo "User with user name: $username is cancelled from site"
      cancelled_users+=" $username"
    done
    echo "Finished processing cleanup for $current_site"
    echo "---------------------------------------------"
    echo "List of cancelled users:"
    echo $cancelled_users | tr " " "\n"
    echo "============================================="
  done
  # Start removing users from ACSF.
  echo "Removing user from ACSF console for given emails."
  $acsf_remove_user $user_emails
  echo
  # Start removing user from Acquia cloud.
  echo "Removing user from Acquia cloud for given emails."
  $blt_dir/bin/blt acquia-cloud-remove-member $user_emails
else
  echo "Cleanup aborted."
fi
