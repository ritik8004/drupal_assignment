<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Delete indexes for an env.
 */

$env = isset($argv, $argv[1]) ? $argv[1] : '';
if (empty($env)) {
  print 'No env passed as parameter.' . PHP_EOL . PHP_EOL;
  die();
}

require_once __DIR__ . '/../../../vendor/autoload.php';

use AlgoliaSearch\Client;

$languages = [
  'en',
  'ar',
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $env . '_' . $language;
  $index = $client->initIndex($name);
  $settings = $index->getSettings();
  $client->deleteIndex($name);
  sleep(10);

  foreach ($settings['replicas'] ?? [] as $replica) {
    $client->deleteIndex($replica);
  }

  print $name . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
