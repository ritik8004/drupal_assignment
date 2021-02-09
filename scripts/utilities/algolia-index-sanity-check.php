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

use AlgoliaSearch\Client;

$logger = \Drupal::logger('algolia-index-sanity-check');

$algolia_server_config = \Drupal::config('search_api.server.algolia')->get('backend_config');
$algolia_index_config = \Drupal::config('search_api.index.alshaya_algolia_index')->get('options');

$app_id = $algolia_server_config['application_id'];
$app_secret = $algolia_server_config['api_key'];
$index_name = $algolia_index_config['algolia_index_name'];

$client = new Client($app_id, $app_secret);

// $extra contains the command line args.
$operation = $extra[0] ?? 'verify';
$process = $operation === 'delete' ? 'Deletion' : 'Verification';

$logger->notice('@process process has begun.', [
  '@process' => $process,
]);

$algolia_options = [
  'attributesToRetrieve' => [
    'nid',
  ],
  'hitsPerPage' => 1000,
  'page' => 0,
];

$days = $extra[1] ?? 60;
$days = is_numeric($days) ? $days : 60;
$time = strtotime("-$days days");

$algolia_options['numericFilters'] = 'changed<' . $time;

$actual_indexes = [];
foreach (['en', 'ar'] as $lang) {
  $actual_indexes[] = $client->initIndex($index_name . '_' . $lang);
}

foreach ($actual_indexes as $index) {
  $logger->notice('Finding entries with changed older then @days in index @index', [
    '@index' => $index->indexName,
    '@days' => $days,
  ]);

  $page = 0;
  $results = $index->search('', $algolia_options);

  $nids = array_column($results['hits'], 'nid');
  if (empty($nids)) {
    $logger->notice('No items found in the index @index with changed before @days days.', [
      '@index' => $index->indexName,
      '@days' => $days,
    ]);

    continue;
  }

  // Verify skus do not exist in Drupal.
  $query = \Drupal::database()->select('node_field_data', 'nfd');
  $query->condition('nid', $nids, 'IN');
  $query->addField('nfd', 'nid');
  $nids_available_in_system = $query->execute()->fetchCol();

  $nids_to_remove = array_diff($nids, $nids_available_in_system);
  if (empty($nids_to_remove)) {
    $logger->notice('All data is legitimate in the index @index with changed before @days days.', [
      '@index' => $index->indexName,
      '@days' => $days,
    ]);
    continue;
  }

  $logger->notice('NIDs that are in Algolia index @index but not in database: count @count, nids: @nids', [
    '@index' => $index->indexName,
    '@count' => count($nids_to_remove),
    '@nids' => implode(',', $nids_to_remove),
  ]);

  foreach ($nids_to_remove as $nid) {
    $pos = array_search($nid, $nids);
    $object_id = $results['hits'][$pos]['objectID'];

    if ($operation === 'delete') {
      $index->deleteObject($object_id);

      $logger->warning('Removed entry with objectId @object_id from index @index', [
        '@index' => $index->indexName,
        '@object_id' => $object_id,
      ]);
    }
  }
}

$logger->notice('@process completed.', [
  '@process' => $process,
]);
