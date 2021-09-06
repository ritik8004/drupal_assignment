#!/bin/bash

log_message()
{
  message=$1
  echo "$message" | tee -a ${log_file}
  echo
}

log_file=/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/remove-styles.log
server_root="/var/www/html/$AH_SITE_NAME"
slack_file="${server_root}/scripts/deployment/post_to_slack.sh"
docroot="/var/www/html/$AH_SITE_NAME/docroot"

for site in `drush --root=$docroot sfl --fields`
do
  log_message "$site started."
  site_name=`drush --root=$docroot acsf-tools-info | grep $site | cut -f3`

  styles_folder="/var/www/html/$AH_SITE_NAME/docroot/sites/g/files/$site_name/files/styles"
  log_message $styles_folder

  styles_folder_bkp="/var/www/html/$AH_SITE_NAME/docroot/sites/g/files/$site_name/files/styles_bkp"
  log_message $styles_folder_bkp

  mv $styles_folder $styles_folder_bkp
  screen -S remove_styles_${site} -dm bash -c "rm -rf $styles_folder_bkp"

  log_message "$site done."
done
