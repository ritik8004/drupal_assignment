#!/usr/bin/env bash

# Script to create databases within the mysql instance running in the database container.

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_hmkw; GRANT ALL PRIVILEGES ON drupal_alshaya_hmkw.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_hmae; GRANT ALL PRIVILEGES ON drupal_alshaya_hmae.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_hmsa; GRANT ALL PRIVILEGES ON drupal_alshaya_hmsa.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_dhuae; GRANT ALL PRIVILEGES ON drupal_alshaya_dhuae.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_flae; GRANT ALL PRIVILEGES ON drupal_alshaya_flae.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal_alshaya_flkw; GRANT ALL PRIVILEGES ON drupal_alshaya_flkw.* TO 'drupal'@'%' IDENTIFIED by 'drupal';"
