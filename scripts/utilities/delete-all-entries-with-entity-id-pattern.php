<?php

/**
 * @file
 * Script to replace all the objects in Algolia.
 *
 * The first argument is the index_name.
 * The second argument is the langcode.
 *
 * @code
 * To check for mismatch for multiple facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/delete-all-entries-with-entity-id-pattern.php -- "local_hmae" "en"
 *
 * @endcode
 */

use Algolia\AlgoliaSearch\SearchClient;

$logger = \Drupal::logger('algolia-index-sanity-check');

$algolia_server_config = \Drupal::config('search_api.server.algolia')->get('backend_config');
$algolia_index_config = \Drupal::config('search_api.index.alshaya_algolia_index')->get('options');

$app_id = $algolia_server_config['application_id'];
$app_secret = $algolia_server_config['api_key'];
$index_name = implode('_', $extra);
$lang = $extra[1];

$client = SearchClient::create($app_id, $app_secret);

if (empty($index_name) || empty($lang)) {
  $logger->notice('Please enter the index_name and lancode.');
  exit();
}

$client = SearchClient::create($app_id, $app_secret);
try {
  $index = $client->initIndex($index_name);
}
catch (\Exception $e) {
  $logger->warning('Failed to load index: @index. Exception: @message.', [
    '@index' => $index_name,
    '@message' => $e->getMessage(),
  ]);
}
// Get all sku's and node_ids.
$query = \Drupal::database()->select('node__field_skus', 'nfs');
// Use SKU if the configuration is enabled for Search Index.
$index_sku_as_object_id = \Drupal::config('alshaya_search_algolia.settings')->get('index_sku_as_object_id');
if ($index_sku_as_object_id) {
  $query->addField('nfs', 'entity_id', 'nid');
}
else {
  $query->addField('nfs', 'field_skus_value', 'sku');
}
$objectids_to_delete = $query->execute()->fetchCol();
$logger->notice('@count products found.', [
  '@count' => count($objectids_to_delete),
]);
foreach (array_chunk($objectids_to_delete, '100') as $smaller_chunk) {
  $obj_id = [];
  $logger->warning('Removing chunk of 100 records.');
  foreach ($smaller_chunk as $object_id) {
    $obj_id[] = $index_sku_as_object_id ? "entity:node/$object_id:$lang" : $object_id;
  }
  $response = $index->deleteObjects($obj_id);
  $response->wait();
  $logger->warning('Removed entries from index @index, objectID: @objectID, Response: @response.', [
    '@index' => $index->getIndexName(),
    '@objectID' => implode(',', $obj_id),
    '@response' => json_encode($response->getBody(), JSON_THROW_ON_ERROR),
  ]);
}
