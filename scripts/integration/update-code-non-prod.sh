#!/bin/sh

# Usage: scripts/integration/update-code-non-prod.sh
# Example: scripts/integration/update-code-non-prod.sh qa2 qa-build code,db
#
# This is a simplified command-line version of "Update code" ACSF functionality
# located at https://www.alshaya.acsitefactory.com/admin/gardens/site-update/update
#
# We use simplified version that only updates sites for now and only on non-prod environments.
#
# Mandatory parameters:
# env : environment to run update on. Example: dev, pprod, qa2, test.
#       - the api user must exist on this environment.
#       - for security reasons, update of prod environment is *not* supported and must be performed manually through UI
# branch : branch/tag to update. Example: qa-build
# update_type : code or code,db

source $(dirname "$0")/includes/global-api-settings.inc.sh

env="$1"
branch="$2"
update_type="$3"

# add comma to "code,db" if not already entered
if [ "$update_type" == "code,db" ]
then
    update_type="code, db"
fi

curl "https://www.${env}-alshaya.acsitefactory.com/api/v1/update" \
  -v -u ${user}:${api_key} -k -X POST \
  -H 'Content-Type: application/json' \
  -d "{\"sites_ref\": \"${branch}\", \"sites_type\": \"${update_type}\"}"
