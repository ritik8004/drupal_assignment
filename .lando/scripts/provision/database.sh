#!/usr/bin/env bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

cd $SCRIPT_DIR;

# Script to create databases within the mysql instance running in the database container.
for site in $(php db_names.php)
do
  mysql -u root -h database -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_$site;"
done

mysql -u root -h database -e "GRANT ALL PRIVILEGES ON *.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
