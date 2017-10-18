#!/bin/bash
#
# Shell script to run post-provisioning and configure varnish.
#
# This script updates Varnish VCL and restarts service.

PROJECT_DIR=/var/www/alshaya

# Copy the default Vagrant configuration required in local.
sudo cp -a $PROJECT_DIR/architecture/varnish/varnish-4-box.vcl /etc/varnish/default.vcl

# Copy the template code to default vcl.
cat $PROJECT_DIR/architecture/varnish/varnish-4.vcl | sudo tee -a /etc/varnish/default.vcl > /dev/null

# Copy extra required functions to default vcl.
cat $PROJECT_DIR/architecture/varnish/varnish-4-extras.vcl | sudo tee -a /etc/varnish/default.vcl > /dev/null

# Restart Varnish.
sudo service varnish stop
sudo service varnish start
