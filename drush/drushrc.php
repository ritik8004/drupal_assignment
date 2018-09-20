<?php
ini_set('memory_limit', '2048M');
// Load a drushrc.php configuration file from the current working directory.
$options['config'][] = realpath(__DIR__ . '/../vendor/acquia/blt/drush/drushrc.php');

if (file_exists(__DIR__ . '/../docroot/sites/default/local.drushrc.php')) {
  require __DIR__ . '/../docroot/sites/default/local.drushrc.php';
}

// Add project-specific drush configuration below.
// @see https://github.com/acquia/blt/tree/8.x/drush/drushrc.php For examples
// of valid statements for a Drush runtime config (drushrc) file.

/**
 * Implements hook_drush_sitealias_alter().
 *
 * Allow using only site code instead of full url in drush commands.
 * e.g. drush @alshaya.local -l hmkw status
 *      instead of drush @alshaya.local -l local.alshaya-hmkw.com status
 * e.g. drush @alshaya.01test -l hmkw status
 *      instead of drush @alshaya.01test -l hmkw-test.factory.alshaya.com status
 */
function alshaya_drush_sitealias_alter(&$alias_record) {
  $cli = drush_get_context('cli');

  // Local.
  if ($alias_record['#name'] == 'alshaya.local') {
    if (isset($cli['uri']) && strpos($cli['uri'], '.com') === FALSE) {
      $cli['uri'] = 'local.alshaya-' . $cli['uri'] . '.com';
      drush_set_context('cli', $cli);
    }
  }
  // Production.
  elseif ($alias_record['#name'] === 'alshaya.01live') {
    if (isset($cli['uri']) && strpos($cli['uri'], '.com') === FALSE) {
      $cli['uri'] = $cli['uri'] . '.factory.alshaya.com';
      drush_set_context('cli', $cli);
    }
  }
  // Non-prod cloud envs.
  elseif (strpos($alias_record['#name'], 'alshaya.01') === 0) {
    if (isset($cli['uri']) && strpos($cli['uri'], '.com') === FALSE) {
      $env = substr($alias_record['#name'], strlen('alshaya.01'));
      $cli['uri'] = $cli['uri'] . '-' . $env . '.factory.alshaya.com';
      drush_set_context('cli', $cli);
    }
  }
}
