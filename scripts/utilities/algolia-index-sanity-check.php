<?php

/**
 * @file
 * Script to remove all the objects in Algolia which are not in Drupal.
 *
 * The first argument is the delete option. If empty string is passed, we just
 * check for mismatch but do not delete.
 *
 * The second argument is the number of days to check for.
 * By default we check for 60 days products.
 *
 * E.g.
 *
 * @code
 * To check for mismatch for multiple facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/algolia-index-sanity-check.php -- "verify" "60"
 *
 * To delete mismatching skus from Algolia index for multiple facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/algolia-index-sanity-check.php -- "delete" "60"
 * @endcode
 */

use Algolia\AlgoliaSearch\SearchClient;

$logger = \Drupal::logger('algolia-index-sanity-check');

$algolia_server_config = \Drupal::config('search_api.server.algolia')->get('backend_config');
$algolia_index_config = \Drupal::config('search_api.index.alshaya_algolia_index')->get('options');
// Use SKU as objectID if the configuration is enabled for Search Index.
$index_sku_as_object_id = \Drupal::config('alshaya_search_algolia.settings')->get('index_sku_as_object_id');

$app_id = $algolia_server_config['application_id'];
$app_secret = $algolia_server_config['api_key'];
$index_name = $algolia_index_config['algolia_index_name'];

$client = SearchClient::create($app_id, $app_secret);

// $extra contains the command line args.
$operation = $extra[0] ?? 'verify';
$process = $operation === 'delete' ? 'Deletion' : 'Verification';

$logger->notice('@process process has begun.', [
  '@process' => $process,
]);

$algolia_options = [
  'attributesToRetrieve' => [
    'objectID',
    'nid',
    'sku',
  ],
];

$indexes = [];
foreach (['en', 'ar'] as $lang) {
  $indexes[$lang] = implode('_', [$index_name, $lang]);
}
$indexes['product_list'] = implode('_', [$index_name, 'product_list']);

foreach ($indexes as $type => $index_name) {
  $logger->notice('Browsing all entries in index @index', [
    '@index' => $index_name,
  ]);

  try {
    $index = $client->initIndex($index_name);
    $results = $index->browseObjects($algolia_options);
  }
  catch (\Exception $e) {
    $logger->warning('Failed to load or get results for index: @index. Exception: @message.', [
      '@index' => $index_name,
      '@message' => $e->getMessage(),
    ]);
    continue;
  }

  $data_in_algolia = [];
  foreach ($results as $row) {
    // If index_sku_as_object_id is enabled use SKU for search index.
    $data_in_algolia[$row['objectID']] = $type === 'product_list' || $index_sku_as_object_id
      ? $row['sku']
      : $row['nid'];
  }

  $data_in_algolia = array_filter($data_in_algolia);

  if (empty($data_in_algolia)) {
    $logger->notice('No items found in the index @index.', [
      '@index' => $index->getIndexName(),
    ]);

    continue;
  }

  // Verify skus do not exist in Drupal.
  $query = \Drupal::database()->select('node__field_skus', 'nfs');

  if ($type === 'product_list' || $index_sku_as_object_id) {
    $query->condition('field_skus_value', $data_in_algolia, 'IN');
    $query->addField('nfs', 'field_skus_value', 'sku');
  }
  else {
    $query->condition('entity_id', $data_in_algolia, 'IN');
    $query->addField('nfs', 'entity_id', 'nid');
  }

  $data_available_in_system = $query->execute()->fetchCol();

  $data_to_remove = array_diff($data_in_algolia, array_filter($data_available_in_system));
  if (empty($data_to_remove)) {
    $logger->notice('All data is legitimate in the index @index.', [
      '@index' => $index->getIndexName(),
    ]);

    continue;
  }

  $logger->notice('Products that are in Algolia index @index but not in database: count @count, IDs: @ids', [
    '@index' => $index->getIndexName(),
    '@count' => count($data_to_remove),
    '@ids' => implode(', ', $data_to_remove),
  ]);

  if ($operation === 'delete') {
    $object_ids = [];

    $algolia_object_ids = array_flip($data_in_algolia);
    foreach ($data_to_remove as $id) {
      $object_ids[] = $algolia_object_ids[$id];
    }

    $response = $index->deleteObjects($object_ids);
    $response->wait();

    $logger->warning('Removed entries from index @index, objectIDs: @objectIDs, Response: @response.', [
      '@index' => $index->getIndexName(),
      '@objectIDs' => implode(', ', $object_ids),
      '@response' => json_encode($response->getBody(), JSON_THROW_ON_ERROR),
    ]);
  }
}

$logger->notice('@process completed.', [
  '@process' => $process,
]);
