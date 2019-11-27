#!/bin/bash
#
# Example shell script to run post-provisioning.
#
# This script copies the appropriate key files to configure Oauth.

SOURCE_DIR="/var/www/alshaya/box/ssl"
DESTINATION="/home/vagrant"

sudo cp -r $SOURCE_DIR $DESTINATION
sudo service apache2 restart
