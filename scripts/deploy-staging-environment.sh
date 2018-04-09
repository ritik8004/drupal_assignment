#!/bin/sh

# Usage: scripts/deploy-staging-environment.sh "target_env" "site_ids"
# Example usage: scripts/deploy_staging_environment.sh qa2 201,311
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

user=integration
api_key=960af8b40bb9c44202f99588dfd4136bfebd0b59

# "dev" or "test" or "uat" or "pprod" or "live" or "qa2".
to_acsf_environment="$1"
sites="$2"

curl 'https://www.alshaya.acsitefactory.com/api/v1/stage' \
    -X POST -H 'Content-Type: application/json' \
    -d "{\"to_env\": \"${to_acsf_environment}\", \"sites\": [ ${sites} ], \"detailed_status\": true}" \
    -v -u $user:$api_key
