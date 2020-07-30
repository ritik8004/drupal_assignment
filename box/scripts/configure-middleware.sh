#!/bin/bash
#
# Example shell script to run post-provisioning.
#
# This script creates the default .env file for middleware in HOME directory.

ENV_FILE=/home/vagrant/settings/.env

if [ ! -e "$ENV_FILE" ]; then
  mkdir -p /home/vagrant/settings
  echo "APP_ENV=dev" > $ENV_FILE
  echo "APP_SECRET=secret" >> $ENV_FILE

  cd /var/www/alshaya/docroot/middleware
  composer install
else
  exit 0
fi
