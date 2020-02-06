<?php
// @codingStandardsIgnoreFile

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

$app_id = 'testing24192T8KHZ';
$app_secret_admin = '81c93293993d87fb67f2af22749ecbeb';

$brands = [
  'bbwkw',
  'bbwsa',
  'bbwae',
];

$envs = [
  'local',
  '01dev',
  '01dev2',
  '01dev3',
  '01qa2',
];

global $languages;

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
