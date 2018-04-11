#!/bin/sh

# Usage: scripts/integration/deploy-staging-environment.sh "target_env" "site_ids"
# Example usages:
# scripts/integration/deploy-staging-environment.sh qa2 201,311
# scripts/integration/deploy-staging-environment.sh pprod 321,296,291,246,196,311,336,366,306,351,361,201,256,251,346,376
#
# This is a command-line version of "Deploy staging environment" ACSF functionality
# located at https://www.alshaya.acsitefactory.com/admin/gardens/staging/deploy
#
# Mandatory parameters:
# target_env : target environment. Example: uat
# site_ids : comma-separated list of site IDs to deploy (in the first version). Example: 201,311
#
# You will find the ids of sites on Acquia Cloud UI on site staging selector.
# Here are IDs of some most common sites:
#
# bbkw - 321
# bbwuae - 296
# dhuae - 291
# mckw - 246
# mcuae - 196
# mcksaholding - 311
# mcsa - 336
# mcae - 366
# hmkw - 306
# hmae - 351
# hmsa - 361
# pbksa - 201
# pbae - 256
# pbkw - 251
# tzkw - 346
# vskw - 376

source $(dirname "$0")/includes/global-api-settings.inc.sh

# "dev" or "dev2" or "dev3" or "test" or "uat" or "pprod" or "qa2".
to_acsf_environment="$1"
sites="$2"

curl 'https://www.alshaya.acsitefactory.com/api/v1/stage' \
    -X POST -H 'Content-Type: application/json' \
    -d "{\"to_env\": \"${to_acsf_environment}\", \"sites\": [ ${sites} ], \"detailed_status\": true}" \
    -v -u $user:$api_key
