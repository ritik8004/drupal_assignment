#!/bin/sh

# Usage: scripts/wbm2cm.sh "alias" "site"

# "@alshaya.local".
alias="$1"

# "local.alshaya-mckw.com" or "local.alshaya-hmkw.com".
site="-l $2"

# Clear cache before running updb.
echo "==== Clear cache ===="
drush $alias $site cr

# Run updb.
echo "==== Running updb on: $site ===="
drush $alias $site updb -y

# Enable wbm2cm to switch from workbench_moderation to content_moderation.
echo "==== Enable wbm2cm module ===="
drush $alias $site en -y wbm2cm

# Run drush cmd available from wbm2cm to migrate workbench_moderation content.
echo "==== Running wbm2cm-migrate as the command is available. ===="
drush $alias $site wbm2cm-migrate

# After successful migration uninstall module.
echo "==== Disable wbm2cm module ===="
drush $alias $site pmu -y wbm2cm
