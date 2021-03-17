#!/bin/bash
#
# This script manually stages given sites from production to given environment.
#
# ./manual-stage.sh "mckw,mcsa,hmkw,hmae,pbae,vsae,vskw,bbwae" 01dev3
# ./manual-stage.sh "mckw,mcsa,hmkw,hmae,pbae" 01dev3 reset
# ./manual-stage.sh "hmkw,hmae,pbae" 01dev3 iso
# ./manual-stage.sh "hmkw,hmae,pbae" 01dev3 proxy

sites="$1"
target_env="$2"
type="$3"
user=`whoami`

target_alias=`drush sa | grep "@$user.$target_env\$"`
if [[ -z "$target_alias" ]]; then
  echo "Invalid target env $target_env"
  exit
fi

if [[ -z "$type" ]]; then
  type="iso"
fi

if [[ ! "$type" == "reset" && ! "$type" == "iso" && ! "$type" == "proxy" ]]; then
  echo "3rd parameter is either 'iso' or 'reset' or 'proxy'"
  exit
fi

target_root=`drush sa $target_alias | grep root | cut -d"'" -f4`
target_remote_user=`drush sa $target_alias | grep remote-user | cut -d"'" -f4`
target_remote_host=`drush sa $target_alias | grep remote-host | cut -d"'" -f4`
target="$target_remote_user@$target_remote_host"

source_domain=factory.alshaya.com
target_domain=${target_env:2}.$source_domain

echo "Preparing list of sites to stage..."
valid_sites=""
for current_site in $(echo $sites | tr "," "\n")
do
  cd /var/www/html/${AH_SITE_NAME}/docroot
  found=$(drush -l $current_site.$source_domain status | grep "DB driver")
  if [ -z "$found" ]; then
    echo "Impossible to find site $current_site on live."
    continue
  fi

  cd ~
  found=$(drush $target_alias ssh "drush -l $current_site-$target_domain status" | grep "DB driver")
  if [ -z "$found" ]; then
    echo "Impossible to find site $current_site on $target_env."
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

echo "Dumping databases..."
mkdir -p ~/manual-stage

# Dump databases.
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  echo "Dumping databases for $current_site"
  drush -l $current_site.$source_domain sql-dump --result-file=~/manual-stage/$current_site.sql --skip-tables-key=common --gzip
done


echo
echo "Copying the dump files to target env..."
ssh $target 'mkdir -p ~/manual-stage'
scp ~/manual-stage/* $target:~/manual-stage/
rm -rf ~/manual-stage


echo
echo "Importing the dumps on target env..."
ssh $target 'gunzip ~/manual-stage/*.gz'
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  uri="https://$current_site-$target_domain"

  # Remove trailing numbers to get exact site code.
  site_code=${current_site//[0-9]/}

  # Remove the last two characters which are always the country code.
  brand_code=${site_code%??}

  echo
  echo "Droppping and importing database again for $current_site"
  ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot; drush -l $uri sql-drop -y; drush -l $uri sql-cli < ~/manual-stage/$current_site.sql"

  echo "Execute drush status for the target site to ensure config reset is executed after database restore"
  ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot; drush -l $uri status"

  echo "Executing post-db-copy operations on $current_site"
  site_db=`drush acsf-tools-info | grep $current_site | cut -d"	" -f3`
  ssh $target "php -f /var/www/html/$AH_SITE_GROUP.$target_env/hooks/common/post-db-copy/000-acquia_required_scrub.php $AH_SITE_GROUP $target_env $site_db"
  ssh $target "php -f /var/www/html/$AH_SITE_GROUP.$target_env/hooks/common/post-db-copy/0000-clear_cache_tables.php $AH_SITE_GROUP $target_env $site_db"

  echo "Creating QA accounts"
  ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot; drush -l $uri alshaya-create-qa-accounts"

  if [[ "$type" == "reset" ]]; then
    echo
    echo "Initiating reset-individual-site-post-stage on $current_site in a screen."
    ssh $target "screen -S $current_site -dm bash -c \"cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot; ../scripts/staging/reset-individual-site-post-stage.sh '$current_site'\""
  fi
done


echo
echo "Syncing files with target env"
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  echo
  echo "Syncing files with target env for $current_site"
  echo
  uri="https://$current_site-$target_domain"
  siteUri=`drush acsf-tools-list --fields=domains | grep -A3 "^$current_site$" | tail -n 1 | cut -d' ' -f6`
  site_files=`drush acsf-tools-info | grep $current_site | cut -d"	" -f3`
  files_folder="sites/g/files/$site_files/files"
  brand_files_folder="sites/g/files/$brand_code"

  target_files_folder="/var/www/html/$AH_SITE_GROUP.$target_env/docroot/$files_folder"
  target_brand_files_folder="/var/www/html/$AH_SITE_GROUP.$target_env/docroot/sites/g/files/$brand_code"

  rsync -a $files_folder/* --exclude 'css' --exclude 'js' --exclude 'styles' --exclude 'media' --exclude 'assets' --exclude 'assets-shared' $target:$target_files_folder

  if [[ "$type" == "iso" ]]; then
    echo
    echo "Initiating rsync of product media files in screen rsync_${current_site}_${target_env}"
    screen -S rsync_media_${current_site}_${target_env} -dm bash -c "rsync -auv $files_folder/media $target:$target_files_folder --delete"
    screen -S rsync_brand_${current_site}_${target_env} -dm bash -c "rsync -auv $brand_files_folder/* --exclude 'styles' $target:$target_brand_files_folder  --delete"
    ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot ; drush -l $uri sapi-c acquia_search_index"
    ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot ; drush -l $uri sapi-c alshaya_algolia_index"
    ssh $target "screen -S $current_site -dm bash -c \"cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot; drush -l $uri sapi-i\""
  fi

  if [[ "$type" == "proxy" ]]; then
    echo
    echo "Enabling stage file proxy for $current_site to https://${siteUri}"
    ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot ; drush -l $uri pm:enable stage_file_proxy"
    ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot ; drush -l $uri cset stage_file_proxy.settings origin https://${siteUri} -y"
    ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot ; drush -l $uri cset stage_file_proxy.settings origin_dir $files_folder -y"
    ssh $target "cd /var/www/html/$AH_SITE_GROUP.$target_env/docroot ; drush -l $uri cset stage_file_proxy.settings hotlink 1 -y"
  fi

done
ssh $target 'rm -rf ~/manual-stage'
