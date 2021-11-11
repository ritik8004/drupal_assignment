<?php
// @codingStandardsIgnoreFile

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

$query = $name . '_query';
algolia_delete_query_suggestion($app_id, $app_secret_admin, $query);
$queryIndex = $client->initIndex($query);
$queryIndex->delete();

$index = $client->initIndex($name);
$settings = $index->getSettings();
$index->delete();
sleep(10);

foreach ($settings['replicas'] ?? [] as $replica) {
  $replicaIndex = $client->initIndex($replica);
  $replicaIndex->delete();
}

print $name . PHP_EOL;
print $query . PHP_EOL;
print implode(PHP_EOL, $settings['replicas']);
print PHP_EOL . PHP_EOL . PHP_EOL;
