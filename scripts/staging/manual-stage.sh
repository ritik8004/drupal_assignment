#!/bin/bash
#
# This script manually stages given sites from production to given environment.
#
# ./manual-stage.sh "mckw,mcsa,hmkw,hmae,pbae,vsae,vskw,bbwae" "01dev3"

sites=$1
target_env="$2"

# Normalize the target environment.
if [ ${target_env:0:2} != "01" ]; then
  target_env="01$target_env"
fi

echo "Preparing list of sites to stage..."
cd /var/www/html/${AH_SITE_NAME}/docroot
valid_sites=""
for current_site in $(echo $sites | tr "," "\n")
do
  found=$(drush @$current_site.01live status | grep "Drupal version")

  if [ -z "$found" ]; then
    echo "Impossible to find site $current_site on live."
    continue
  fi

  found=$(drush @$current_site.$target_env status | grep "Drupal version")

  if [ -z "$found" ]; then
    echo "Impossible to find site $current_site on $target_env."
  fi

  valid_sites="$valid_sites,$current_site"
done
echo "Final list of sites: $valid_sites"
echo

# In case not site id have been found, stop here.
if [ -z "$valid_sites" ]; then
  exit
fi


echo "Dumping databases..."
mkdir -p /tmp/manual-stage

# Dump databases.
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  echo $current_site
  drush -l $current_site.factory.alshaya.com sql-dump --result-file=/tmp/manual-stage/$current_site.sql --skip-tables-key=common --gzip
done


echo
echo "Copying the dump files to target env..."
remote_user=`drush sa @alshaya.$target_env | grep remote-user | cut -d" " -f 4`
remote_host=`drush sa @alshaya.$target_env | grep remote-host | cut -d" " -f 4`
ssh $remote_user@$remote_host 'mkdir -p /tmp/manual-stage'
scp /tmp/manual-stage/* $remote_user@$remote_host:/tmp/manual-stage/
rm -rf /tmp/manual-stage


echo
echo "Importing the dumps on target env..."
ssh $remote_user@$remote_host 'gunzip /tmp/manual-stage/*.gz'
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  echo $current_site
  uri=`drush sa @$current_site.$target_env | grep uri | cut -d" " -f 4`

  drush @$current_site.$target_env sql-drop -y
  ssh $remote_user@$remote_host "cd /var/www/html/alshaya.$target_env/docroot ; drush -l $uri sql-cli < /tmp/manual-stage/$current_site.sql"

  site_db=`drush acsf-tools-info | grep $current_site | cut -d"	" -f3`
  ssh $remote_user@$remote_host "php -f /var/www/html/alshaya.$target_env/hooks/common/post-db-copy/000-acquia_required_scrub.php alshaya $target_env $site_db"
  ssh $remote_user@$remote_host "php -f /var/www/html/alshaya.$target_env/hooks/common/post-db-copy/0000-clear_cache_tables.php alshaya $target_env $site_db"
done


echo
echo "Syncing files with target env"
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  echo $current_site
  site_files=`drush acsf-tools-info | grep $current_site | cut -d"	" -f3`
  files_folder="sites/g/files/$site_files/files"
  target_files_folder="/var/www/html/alshaya.$target_env/docroot/$files_folder"

  rsync -va $files_folder/swatches $remote_user@$remote_host:$target_files_folder
  rsync -va $files_folder/20* $remote_user@$remote_host:$target_files_folder
  rsync -va $files_folder/labels $remote_user@$remote_host:$target_files_folder
  rsync -va $files_folder/maintenance_mode_image $remote_user@$remote_host:$target_files_folder
  rsync -va $files_folder/media-icons $remote_user@$remote_host:$target_files_folder
  rsync -vt $files_folder/* $remote_user@$remote_host:$target_files_folder
done
ssh $remote_user@$remote_host 'rm -rf /tmp/manual-stage'
