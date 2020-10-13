#!/bin/bash

# Create a file "acquia_cloud_api_creds.php" in home directory.
# Add the creds of api in the file same as "credentials.php".
# Run the script like "./twig_cache_flush.sh ENV_UUID_HERE"

env_uuid="$1"

# If env uuid is not provided.
if [[ -z "${env_uuid}" ]]; then
  echo "Please provide the Environment UUID."
  exit
fi

# PATH of the PHP script on server.
server_script_path=/var/www/html/${AH_SITE_GROUP}.${AH_SITE_ENVIRONMENT}/scripts/cloud_config/getWebServers.php

# Get the servers info.
servers=$(php ${server_script_path} ${env_uuid})

for server in ${servers}
do
  echo "Twig flushing for server: ${server}"
  # If same web-head, no need to ssh.
  if [[ "${server}" == "${HOSTNAME}" ]]; then
    cd /var/www/html/${AH_SITE_NAME}/docroot
    drush sfml ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll()' &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-cron.log
  else
    connection="${AH_SITE_GROUP}.${AH_SITE_ENVIRONMENT}@${server}"
    # SSH on server. Move to the Project directory. Run DRUSH command on all sites.
    ssh -t ${connection} 'cd /var/www/html/${AH_SITE_GROUP}.${AH_SITE_ENVIRONMENT}/docroot; drush sfml ev '\''\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll()'\'' &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-cron.log'
  fi
done
