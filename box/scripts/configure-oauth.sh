#!/bin/bash
#
# Example shell script to run post-provisioning.
#
# This script copies the appropriate key files to configure Oauth.

SOURCE_DIR="/var/www/alshaya/private"
DESTINATION="/var/www/alshaya/box"

sudo cp -a $SOURCE_DIR/travis_acm $DESTINATION/alshaya_acm
sudo cp -a $SOURCE_DIR/travis_acm.pub $DESTINATION/alshaya_acm.pub

