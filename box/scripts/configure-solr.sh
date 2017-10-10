#!/bin/bash
#
# Example shell script to run post-provisioning.
#
# This script configures the default Apache Solr search core to use one of the
# Drupal Solr module's configurations. This shell script presumes you have
# `solr` in the `installed_extras`, and is currently set up for the D8 versions
# of Apache Solr Search or Search API Solr.

SOLR_SETUP_COMPLETE_FILE=/etc/drupal_vm_solr_config_complete

# Search API Solr module.
PROJECT_DIR=/var/www/alshaya
SOLR_MODULE_DIR=$PROJECT_DIR/docroot/modules/contrib
SOLR_MODULE_NAME=search_api_solr

# Check to see if we've already performed this setup.
if [ ! -e "$SOLR_SETUP_COMPLETE_FILE" ]; then

  # Copy the Solr configuration into place over the default `collection1` core.
  sudo cp -a $SOLR_MODULE_DIR/$SOLR_MODULE_NAME/solr-conf/4.x/. /var/solr/collection1/conf/

  # Copy this projects overrides into the `collection1` core.
  sudo cp -a $PROJECT_DIR/architecture/solr/. /var/solr/collection1/conf/

  # Adjust the autoCommit time so index changes are committed in 1s.
  #sudo sed -i 's/\(<maxTime>\)\([^<]*\)\(<[^>]*\)/\11000\3/g' /var/solr/collection1/conf/solrconfig.xml

  # Fix file permissions.
  sudo chown -R solr:solr /var/solr/collection1/conf

  # Restart Apache Solr.
  sudo service solr restart

  # Create a file to indicate this script has already run.
  sudo touch $SOLR_SETUP_COMPLETE_FILE
else
  exit 0
fi
