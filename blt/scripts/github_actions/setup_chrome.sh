#!/usr/bin/env bash

set -ev
set -x

wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
apt -y update
apt -y -f install ./google-chrome-stable_current_amd64.deb
rm google-chrome-*.deb

set +v
