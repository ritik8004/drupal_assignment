<?php

/**
 * @file
 * Github Actions environment specific settings.
 */

/**
 * Overwrite CI default database host name.
 */
if (isset($_ENV['GITHUB_ACTIONS'])) {
  $databases['default']['default']['host'] = 'database';
  $databases['default']['default']['password'] = 'drupal';
  $databases['default']['default']['username'] = 'drupal';
  $databases['default']['default']['database'] = 'drupal';
}
