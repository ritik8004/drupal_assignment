#!/bin/sh

# Usage: scripts/wbm2cm.sh "alias" "site"

# "@alshaya.local".
alias="$1"

# "local.alshaya-mckw.com" or "local.alshaya-hmkw.com".
site="-l $2"

echo "Clear cache"
drush $alias $site cr

echo "Running updb on: $site"
drush $alias $site updb -y

echo "Enable wbm2cm module"
drush $alias $site en -y wbm2cm

echo "Running wbm2cm-migrate as the command is available."
drush $alias $site wbm2cm-migrate
