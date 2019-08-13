<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Delete indexes for an prefix.
 */

/**
 * How to use this:
 *
 * Usage: php deleteIndex.php [brand] [env] [app_id] [app_secret_admin]
 *
 * Deletes index along with it's query suggestion and all replicas in all
 * languages.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

use AlgoliaSearch\Client;

$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $prefix . '_' . $language;

  $query = $name . '_query';
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
