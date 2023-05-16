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
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/delete-all-entries-with-entity-id-pattern.php -- "local_hmae" "en" "500"
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
$size = isset($extra[2]) ? (int) $extra[2] : 500;

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
  $qstring = "nfs.field_skus_value = did.object_id AND did.langcode = '$lang'";
  $query->addField('nfs', 'entity_id', 'nid');
  // Left join to get all the values from sku table.
  $query->leftJoin('deleted_objectids', 'did', $qstring);
}
else {
  $qstring = "nfs.field_skus_value = did.object_id AND did.langcode = '$lang'";
  $query->addField('nfs', 'field_skus_value', 'sku');
  // Left join to get all the values from sku table.
  $query->leftJoin('deleted_objectids', 'did', $qstring);
}
// Exclude records that got processed.
$query->condition('did.object_id', NULL, 'IS NULL');
$objectids_to_delete = $query->execute()->fetchCol();
$logger->notice('@count products found.', [
  '@count' => count($objectids_to_delete),
]);
foreach (array_chunk($objectids_to_delete, $size) as $smaller_chunk) {
  $obj_id = [];
  $logger->warning('Removing chunk of @size records.', [
    '@size' => $size,
  ]);
  foreach ($smaller_chunk as $object_id) {
    $obj_id[] = $index_sku_as_object_id ? "entity:node/$object_id:$lang" : $object_id;
    \Drupal::database()->insert('deleted_objectids')
      ->fields(['object_id', 'langcode'])
      ->values([$object_id, $lang])
      ->execute();
  }
  try {
    $response = $index->deleteObjects($obj_id);
    $response->wait();
    $logger->warning('Removed entries from index @index, objectID: @objectID, Response: @response.', [
      '@index' => $index->getIndexName(),
      '@objectID' => implode(',', $obj_id),
      '@response' => json_encode($response->getBody(), JSON_THROW_ON_ERROR),
    ]);
  }
  catch (\Exception $e) {
    $logger->error("Failed to delete @object_ids from index. Exception: @error", [
      '@error' => $e->getMessage(),
      '@object_ids' => implode(',', $obj_id),
    ]);
  }

}

$logger->notice('Finished operation');
