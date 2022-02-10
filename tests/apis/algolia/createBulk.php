<?php
// phpcs:ignoreFile

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

// These are the old "Sanbox" credentials, which we cannot use to create new
// indices as we have exceeded the Query Suggestions limit.
// $app_id = 'testing24192T8KHZ';
// $app_secret_admin = '81c93293993d87fb67f2af22749ecbeb';
// These are the new sandbox "Acquia Dev" credentials.
$app_id = 'Q402AB9LJF';
$app_secret_admin = '0fe92997d79ed56790912b14d3ecbc5f';

$sites = [
  'vskw',
  'vssa',
  'vsae',
  'vsqa'
];

$envs = [
  'local',
  '01dev',
  '01dev2',
  '01dev3',
  '01qa2',
];

global $languages;
global $migrate_index;

// To be set to TRUE when we are moving indices from one Algolia app to
// another.
$migrate_index = FALSE;

foreach ($envs as $env) {
  foreach ($sites as $site) {
    $prefix = $env . '_' . $site;
    foreach ($languages as $language) {
      try {
        algolia_create_index($app_id, $app_secret_admin, $language, $prefix);
      }
      catch (\Exception $e) {
        print 'Error occurred for ' . $prefix . '_' . $language . PHP_EOL;
        print $e->getMessage() . PHP_EOL . PHP_EOL;
      }
    }
  }
}

exit;

// Settings for live app.
$app_id = '6TOQSJY0O6';
$app_secret_admin = ''; // Admin Key.

$envs = [
  '01test',
  '01uat',
  '01pprod',
  '01live',
];

foreach ($envs as $env) {
  foreach ($brands as $brand) {
    $prefix = $env . '_' . $brand;
    foreach ($languages as $language) {
      try {
        algolia_create_index($app_id, $app_secret_admin, $language, $prefix);
      }
      catch (\Exception $e) {
        print 'Error occurred for ' . $prefix . '_' . $language . PHP_EOL;
        print $e->getMessage() . PHP_EOL . PHP_EOL;
      }
    }
  }
}
