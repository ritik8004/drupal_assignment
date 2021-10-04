#!/usr/bin/env bash

# Solution taken from https://github.com/nodesource/distributions/issues/866

# Remove anything with nodejs.
apt-get purge nodejs -y

# Pin the old version.
echo "Package: *" >> /etc/apt/preferences.d/nodesource
echo "Pin: origin deb.nodesource.com" >> /etc/apt/preferences.d/nodesource
echo "Pin-Priority: 600" >> /etc/apt/preferences.d/nodesource

# Setup 8.x.
curl -sL https://deb.nodesource.com/setup_8.x | bash -
apt-get install nodejs -y
