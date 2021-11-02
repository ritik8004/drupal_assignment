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
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/algolia-index-sanity-check-listing.php -- "verify" "60"
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/algolia-index-sanity-check-listing.php -- "delete" "60"
 * @endcode
 */

use Algolia\AlgoliaSearch\SearchClient;

$logger = \Drupal::logger('algolia-index-sanity-check-listing');

$algolia_index_config = \Drupal::config('search_api.index.alshaya_algolia_product_list_index');
if (empty($algolia_index_config) || !($algolia_index_config->get('status'))) {
  $logger->notice('Algolia listing page index not enabled, skipping.');
  return;
}

$algolia_server_config = \Drupal::config('search_api.server.algolia')->get('backend_config');
$algolia_index_config = $algolia_index_config->get('options');

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
    'sku',
  ],
  'hitsPerPage' => 1000,
  'page' => 0,
];

$days = $extra[1] ?? 60;
$days = is_numeric($days) ? $days : 60;
$time = strtotime("-$days days");

$algolia_options['numericFilters'] = 'changed.en<' . $time;

$indexes[$index_name] = implode('_', [
  $index_name,
  'changed_asc',
]);

foreach ($indexes as $actual_index_name => $replica_index_name) {
  $replica = $client->initIndex($replica_index_name);
  $actual = $client->initIndex($actual_index_name);

  $logger->notice('Finding entries with changed older then @days in index @index', [
    '@index' => $replica->getIndexName(),
    '@days' => $days,
  ]);

  $results = $replica->search('', $algolia_options);

  $skus = array_column($results['hits'], 'sku');
  if (empty($skus)) {
    $logger->notice('No items found in the index @index with changed before @days days.', [
      '@index' => $replica->getIndexName(),
      '@days' => $days,
    ]);

    continue;
  }

  // Verify skus do not exist in Drupal.
  $query = \Drupal::database()->select('acq_sku_field_data', 'asfd');
  $query->condition('sku', $skus, 'IN');
  $query->addField('asfd', 'sku');
  $skus_available_in_system = $query->execute()->fetchCol();

  $skus_to_remove = array_diff($skus, $skus_available_in_system);
  if (empty($skus_to_remove)) {
    $logger->notice('All data is legitimate in the index @index with changed before @days days.', [
      '@index' => $replica->getIndexName(),
      '@days' => $days,
    ]);
    continue;
  }

  $logger->notice('SKUs that are in Algolia index @index but not in database: count @count, SKUs: @skus.', [
    '@index' => $replica->getIndexName(),
    '@count' => count($skus_to_remove),
    '@skus' => implode(',', $skus_to_remove),
  ]);

  if ($operation === 'delete') {
    $actual->deleteObjects($skus_to_remove);

    $logger->warning('Removed entries from index @index for objectIds: @objectIds.', [
      '@index' => $actual->getIndexName(),
      '@objectIds' => $skus_to_remove,
    ]);
  }
}

$logger->notice('@process completed.', [
  '@process' => $process,
]);
