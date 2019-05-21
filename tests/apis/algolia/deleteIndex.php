<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Delete indexes for an env.
 */

/**
 * How to use this:
 *
 * Usage: php deleteIndex.php [env]
 * Example: php deleteIndex.php 01dev
 *
 * Deletes index along with it's query suggestion and all replicas in all
 * languages.
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

  $query = $name . '_query1';
  algolia_delete_query_suggestion($app_id, $app_secret_admin, $query);
  $client->deleteIndex($query);

  $index = $client->initIndex($name);
  $settings = $index->getSettings();
  $client->deleteIndex($name);
  sleep(10);

  foreach ($settings['replicas'] ?? [] as $replica) {
    $client->deleteIndex($replica);
  }

  print $name . PHP_EOL;
  print $query . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
