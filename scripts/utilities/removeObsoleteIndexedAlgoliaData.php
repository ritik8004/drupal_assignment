<?php

/**
 * @file
 * Script to get all the objects in Algolia which are not available in Drupal.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * The first argument is the delete option. If empty string is passed, we just
 * check for mismatch but do not delete.
 * The second argument is the facet filters option. Use comma separated values
 * to provide multiple facet filters.
 *
 * E.g.
 *
 * @code
 * To check for mismatch for multiple facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/removeObsoleteIndexedAlgoliaData.php -- "" "field_category.lvl0:Ladies1,field_category.lvl0:All"
 *
 * To delete mismatching skus from Algolia index for multiple facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/removeObsoleteIndexedAlgoliaData.php -- "delete" "field_category.lvl0:Ladies1,field_category.lvl0:All"
 *
 * To check for mismatch with no facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/removeObsoleteIndexedAlgoliaData.php
 *
 * To delete mismatching skus from Algolia index for multiple facet filters:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/removeObsoleteIndexedAlgoliaData.php -- "delete"
 * @endcode
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use AlgoliaSearch\Client;

$algolia_server_config = \Drupal::config('search_api.server.algolia')->get('backend_config');
$algolia_index_config = \Drupal::config('search_api.index.alshaya_algolia_index')->get('options');

$app_id = $algolia_server_config['application_id'];
$app_secret = $algolia_server_config['api_key'];
$index_name = $algolia_index_config['algolia_index_name'];

$chunk_size = 500;

$client = new Client($app_id, $app_secret);

// $extra contains the command line args.
$is_deletion_to_be_done = (isset($extra[0]) && !empty($extra[0])) ? TRUE : FALSE;
$output_message = $is_deletion_to_be_done ? "Deletion" : "Checking";

echo "$output_message process has begun." . PHP_EOL;

$algolia_options = [
  'attributesToRetrieve' => [
    'sku',
  ],
  'hitsPerPage' => $chunk_size,
  'page' => 0,
];

$facet_filters = (isset($extra[1]) && !empty($extra[1])) ? explode(',', $extra[1]) : [];

if (!empty($facet_filters)) {
  $algolia_options['facetFilters'] = array_values($facet_filters);
}

$actual_indexes = [];
foreach (['en', 'ar'] as $lang) {
  $actual_indexes[] = $client->initIndex($index_name . '_' . $lang);
}

foreach ($actual_indexes as $index) {
  echo PHP_EOL . "Processing index $index->indexName" . PHP_EOL;
  $page = 0;
  $results = $index->search('', $algolia_options);

  // Do not update this variable.
  $skus_to_remove = array_column($results['hits'], 'sku');
  if (empty($skus_to_remove)) {
    echo "No items found in the index for the search criteria provided." . PHP_EOL;
    continue;
  }

  // Verify skus do not exist in Drupal.
  $query = \Drupal::database()->select('acq_sku_field_data', 'afd');
  $query->condition('sku', $skus_to_remove, 'IN');
  $query->addField('afd', 'sku');
  $skus_existing_in_db = $query->execute()->fetchCol();

  $final_skus_to_remove = array_diff($skus_to_remove, $skus_existing_in_db);
  if (empty($final_skus_to_remove)) {
    echo "Nothing to remove from " . $index->indexName . PHP_EOL;
    continue;
  }

  echo "SKUs that are in Algolia index but not in database ............" . PHP_EOL;
  print_r(array_values($final_skus_to_remove));

  foreach ($final_skus_to_remove as $sku) {
    $pos = array_search($sku, $skus_to_remove);
    $object_id = $results['hits'][$pos]['objectID'];

    if ($is_deletion_to_be_done) {
      $index->deleteObject($object_id);
      echo "Deleted sku $sku." . PHP_EOL;
    }
  }

  echo "$output_message done for index $index->indexName." . PHP_EOL;
}

echo PHP_EOL . "$output_message process completed." . PHP_EOL;
