#!/bin/bash
#
# This script manually stages given sites from production to given environment.
#
# soft-stage.sh "mckw,mcsa,hmkw,hmae,pbae,vsae,vskw,bbwae" "01dev3"

sites=$1
target_env="$2"

# Normalize the target environment.
if [ ${target_env:0:2} != "01" ]; then
  target_env="01$target_env"
fi

env=${target_env:2}

# Get list of sites to stage.
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

# In case not site id have been found, move to next batch.
if [ -z "$valid_sites" ]; then
  exit
fi

mkdir -p /tmp/manual-stage

# Dump databases.
cd /var/www/html/${AH_SITE_NAME}/docroot
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  drush -l $current_site.factory.alshaya.com sql-dump --result-file=/tmp/manual-stage/$current_site.sql --skip-tables-key=common --gzip
done

# Copy the files over.
remote_user=`drush sa @alshaya.01pprod | grep remote-user | cut -d" " -f 4`
remote_host=`drush sa @alshaya.01pprod | grep remote-host | cut -d" " -f 4`
ssh $remote_user@$remote_host 'mkdir -p /tmp/manual-stage'
scp /tmp/manual-stage/* $remote_user@$remote_host:/tmp/manual-stage/
rm -rf /tmp/manual-stage

# Import the dumps.
ssh $remote_user@$remote_host 'gunzip /tmp/manual-stage/*.gz'
for current_site in $(echo ${valid_sites:1} | tr "," "\n")
do
  uri=`drush sa @$current_site.01pprod | grep uri | cut -d" " -f 4`

  drush @$current_site.$target_env sql-drop -y
  ssh $remote_user@$remote_host "cd /var/www/html/alshaya.$target_env/docroot ; drush -l $uri sql-cli < /tmp/manual-stage/$current_site.sql"
done
ssh $remote_user@$remote_host '/bin/echo -e "flush_all\nquit" | nc -q1 $(hostname -s) 11211'
ssh $remote_user@$remote_host 'rm -rf /tmp/manual-stage'
