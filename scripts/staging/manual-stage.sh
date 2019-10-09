#!/bin/bash
#
# This script manually stages given sites from production to given environment.
#
# ./manual-stage.sh "mckw,mcsa,hmkw,hmae,pbae,vsae,vskw,bbwae" 01dev3
# ./manual-stage.sh "mckw,mcsa,hmkw,hmae,pbae" 01dev3 reset
# ./manual-stage.sh "hmkw,hmae,pbae" 01dev3 iso

sites="$1"
target_env="$2"

# Normalize the target environment.
if [ ${target_env:0:2} != "01" ]; then
  target_env="01$target_env"
fi

type="$3"
if [[ -z "$type" ]]; then
  type="iso"
fi

if [[ ! "$type" == "reset" && ! "$type" == "iso" ]]; then
  echo "3rd parameter is either 'iso' or 'reset'"
  exit
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
  echo "Dumping databases for $current_site"
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
  uri=`drush sa @$current_site.$target_env | grep uri | cut -d" " -f 4`

  echo
  echo "Droppping and importing database again for $current_site"
  ssh $remote_user@$remote_host "cd /var/www/html/alshaya.$target_env/docroot; drush -l $uri sql-drop -y; drush -l $uri sql-cli < /tmp/manual-stage/$current_site.sql"

  echo "Executing post-db-copy operations on $current_site"
  site_db=`drush acsf-tools-info | grep $current_site | cut -d"	" -f3`
  ssh $remote_user@$remote_host "php -f /var/www/html/alshaya.$target_env/hooks/common/post-db-copy/000-acquia_required_scrub.php alshaya $target_env $site_db"
  ssh $remote_user@$remote_host "php -f /var/www/html/alshaya.$target_env/hooks/common/post-db-copy/0000-clear_cache_tables.php alshaya $target_env $site_db"

  if [[ "$type" == "reset" ]]; then
    echo
    echo "Initiating reset-individual-site-post-stage on $current_site in a screen."
    ssh $remote_user@$remote_host "screen -S $current_site -dm bash -c \"cd /var/www/html/alshaya.$target_env/docroot; ../scripts/staging/reset-individual-site-post-stage.sh '$target_env' '$current_site'\""
  fi
done


echo
echo "Syncing files with target env"
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  echo
  echo "Syncing files with target env for $current_site"
  echo
  uri=`drush sa @$current_site.$target_env | grep uri | cut -d" " -f 4`
  siteUri=`drush sa @$current_site.01live | grep uri | cut -d" " -f 4`
  site_files=`drush acsf-tools-info | grep $current_site | cut -d"	" -f3`
  files_folder="sites/g/files/$site_files/files"
  target_files_folder="/var/www/html/alshaya.$target_env/docroot/$files_folder"

  rsync -a $files_folder/swatches $remote_user@$remote_host:$target_files_folder
  rsync -a $files_folder/20* $remote_user@$remote_host:$target_files_folder
  rsync -a $files_folder/labels $remote_user@$remote_host:$target_files_folder
  rsync -a $files_folder/maintenance_mode_image $remote_user@$remote_host:$target_files_folder
  rsync -a $files_folder/media-icons $remote_user@$remote_host:$target_files_folder
  rsync -t $files_folder/* $remote_user@$remote_host:$target_files_folder

  if [[ "$type" == "iso" ]]; then
    echo
    echo "Enabling stage file proxy for $current_site"
    ssh $remote_user@$remote_host "cd /var/www/html/alshaya.$target_env/docroot ; drush -l $uri pm:enable stage_file_proxy"
    ssh $remote_user@$remote_host "cd /var/www/html/alshaya.$target_env/docroot ; drush -l $uri cset stage_file_proxy.settings origin $siteUri -y"
    ssh $remote_user@$remote_host "cd /var/www/html/alshaya.$target_env/docroot ; drush -l $uri cset stage_file_proxy.settings origin_dir $files_folder -y"
  fi
done
ssh $remote_user@$remote_host 'rm -rf /tmp/manual-stage'

