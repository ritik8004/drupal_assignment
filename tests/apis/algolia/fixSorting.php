<?php
// phpcs:ignoreFile

global $languages;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'prod_keys.php';

use Algolia\AlgoliaSearch\SearchClient;

$client = SearchClient::create($app_id, $app_secret_admin);
$indexes = $client->listIndices();
$indexes = array_column($indexes['items'] ?? [], 'name');

$markets = [
  'kw',
  'sa',
  'ae',
  'eg',
  'bh',
  'qa',
];

foreach ($markets as $market) {
  $site = $brand . $market;
  print $site . PHP_EOL;

  foreach ($languages as $language) {
    $index_name = '01live_' . $site . '_' . $language;
    if (!in_array($index_name, $indexes)) {
      continue;
    }

    $index = $client->initIndex($index_name);
    $settings = $index->getSettings();

    foreach ($sorts as $sort) {
      $replica_name = $index_name . '_' . implode('_', $sort);
      print $replica_name . PHP_EOL;

      if (!in_array($replica_name, $indexes)) {
        print 'Index available but replica missing' . PHP_EOL;
        continue;
      }

      $replica_index = $client->initIndex($replica_name);
      $replica_settings = $replica_index->getSettings();

      $replica_settings['ranking'] = [
          'desc(stock)',
          $sort['direction'] . '(' . $sort['field'] . ')',
        ] + $settings['ranking'];

      $replica_index->setSettings($replica_settings)->wait();
    }
  }
}

print PHP_EOL . PHP_EOL . 'done' . PHP_EOL . PHP_EOL;
