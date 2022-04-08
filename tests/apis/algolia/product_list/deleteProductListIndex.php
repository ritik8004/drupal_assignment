<?php
// phpcs:ignoreFile

/**
 * @file
 * Delete indexes for an prefix.
 */

/**
 * How to use this:
 *
 * Usage: php deleteProductListIndex.php [brand] [env] [app_id] [app_secret_admin]
 *
 * Deletes index along with it's query suggestion and all replicas in all
 * languages.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '../parse_args.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

use Algolia\AlgoliaSearch\SearchClient;

$client = SearchClient::create($app_id, $app_secret_admin);
$name = $prefix . '_' . $product_list_suffix;

try {
  $index = $client->initIndex($name);
  $settings = $index->getSettings();
} catch (\Exception $e) {
  print 'Error occurred for index ' . $name . ': ' . $e->getMessage();
  print PHP_EOL . PHP_EOL;
}

// To delete replica index, first we need to unlink the replica from original.
$index->setSettings([
  'replicas' => []
])->wait();

// Delete replicas.
foreach ($settings['replicas'] ?? [] as $replica) {
  try {
    $replicaIndex = $client->initIndex($replica);
    $replicaIndex->delete()->wait();
  } catch (\Exception $e) {
    print 'Error occurred for index ' . $replica . ': ' . $e->getMessage();
  }
}

// Delete the index after deleting replicas.
$index->delete()->wait();

print $name . PHP_EOL;
print implode(PHP_EOL, $settings['replicas']);
print PHP_EOL . PHP_EOL . PHP_EOL;
