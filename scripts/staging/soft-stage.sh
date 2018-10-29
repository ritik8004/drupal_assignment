#!/bin/bash
#
# This script stage given sites from production to given environment.
#

sites=$1

# Get list of sites to stage.
# Format: "vsae;mckw,mcae,mcsa;hmkw;pbae".
# ";" is to separate stage batches.
# "," is to separate sites in a same batch.
for bash in $(echo $sites | tr ";" "\n")
do
  ids=""
  for site in $(echo $bash | tr "," "\n")
  do
    id=$(php get-site-id-from-name.php "$site")

    if [ $id != 0 ]; then
      ids="$ids,$id"
    fi
  done

  echo $ids
done

# Run ACSF API command to launch stage.
#curl 'https://www.alshaya.acsitefactory.com/api/v2/stage' \
#    -X POST -H 'Content-Type: application/json' \
#    -d '{"to_env": "test", "sites": [96, 191], "wipe_target_environment": false, "synchronize_all_users": false, "detailed_status": true}'
#    -v -u {user_name}:{api_key}


# Wait for the tasks to complete.

# Take database dumps.

# Run updb.

# Clean commerce data.

# Sync commerce data.

