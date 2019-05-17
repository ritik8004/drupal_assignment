<?php

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

$client = new Client('HGR051I5XN', '6fc229a5d5d0f0d9cc927184b2e4af3f');

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
