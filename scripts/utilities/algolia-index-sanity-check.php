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
    'nid',
  ],
  'hitsPerPage' => 1000,
  'page' => 0,
];

$days = $extra[1] ?? 60;
$days = is_numeric($days) ? $days : 60;
$time = strtotime("-$days days");

$algolia_options['numericFilters'] = 'changed<' . $time;

$indexes = [];
foreach (['en', 'ar'] as $lang) {
  $actual_index_name = implode('_', [
    $index_name,
    $lang,
  ]);

  $replica_index_name = implode('_', [
    $actual_index_name,
    'changed_asc',
  ]);

  $indexes[$actual_index_name] = $replica_index_name;
}

foreach ($indexes as $actual_index_name => $replica_index_name) {
  $replica = $client->initIndex($replica_index_name);
  $actual = $client->initIndex($actual_index_name);

  $logger->notice('Finding entries with changed older then @days in index @index', [
    '@index' => $replica->getIndexName(),
    '@days' => $days,
  ]);

  $results = $replica->search('', $algolia_options);

  $nids = array_column($results['hits'], 'nid');
  if (empty($nids)) {
    $logger->notice('No items found in the index @index with changed before @days days.', [
      '@index' => $replica->getIndexName(),
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
      '@index' => $replica->getIndexName(),
      '@days' => $days,
    ]);
    continue;
  }

  $logger->notice('NIDs that are in Algolia index @index but not in database: count @count, nids: @nids', [
    '@index' => $replica->getIndexName(),
    '@count' => count($nids_to_remove),
    '@nids' => implode(',', $nids_to_remove),
  ]);

  if ($operation === 'delete') {
    $object_ids = [];

    foreach ($nids_to_remove as $nid) {
      $pos = array_search($nid, $nids);
      $object_ids[] = $results['hits'][$pos]['objectID'];
    }

    $actual->deleteObjects($object_ids);
    $logger->warning('Removed entries from index @index for objectIds: @objectIds.', [
      '@index' => $actual->getIndexName(),
      '@objectIds' => $object_ids,
    ]);
  }
}

$logger->notice('@process completed.', [
  '@process' => $process,
]);
