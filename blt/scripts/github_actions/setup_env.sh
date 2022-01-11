#!/usr/bin/env bash

set -ev
set -x

# Prepare ssh config for deployment to Acquia Cloud.
mkdir -p ~/.ssh
touch ~/.ssh/config
chmod 600 ~/.ssh/config
# Trust all Acquia git/svn hosts.
printf "Host *.enterprise-g1.hosting.acquia.com\n  StrictHostKeyChecking no\n" >> ~/.ssh/config

# Github actions will run steps as root.
# Related to https://github.community/t/how-to-run-action-as-a-non-root-user/17572
mkdir -p /root/.ssh
cp ~/.ssh/config /root/.ssh/config

# Set the git configuration
git config --global user.name "Github-Actions-CI"
git config --global user.email "noreply@github.com"

blt blt:telemetry:disable --no-interaction

# Up the PHP Memory Limit
touch /usr/local/etc/php/conf.d/docker-php-ext-ci.ini
echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-ext-ci.ini

set +v
