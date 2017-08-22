#!/bin/bash
# This file runs post the deploy build is created, and is ready to be committed.

set -e

deployDir="$1"

# This file is not removed via deploy-exclude-additions.
# Refer https://github.com/acquia/blt/issues/1941
rm $deployDir/docroot/core/install.php
