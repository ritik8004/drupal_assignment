<?php
// @codingStandardsIgnoreFile

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

$app_id = 'testing24192T8KHZ';
$app_secret_admin = '81c93293993d87fb67f2af22749ecbeb';

$brands = [
  'mckw',
  'mcsa',
  'mcae',
  'hmkw',
  'hmsa',
  'hmae',
  'hmeg',
];

$envs = [
  'local',
  '01dev',
  '01dev2',
  '01dev3',
  '01qa2',
  '01test',
];

global $languages;

foreach ($envs as $env) {
  foreach ($brands as $brand) {
    $prefix = $env . '_' . $brand;
    foreach ($languages as $language) {
      algolia_create_index($app_id, $app_secret_admin, $language, $prefix);
    }
  }
}
