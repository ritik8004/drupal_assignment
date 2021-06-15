<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Update index and it's replicas for each language.
 */

/**
 * How to use this:
 *
 * Usage: php updateIndexProductListIndex.php [brand] [env] [app_id] [app_secret_admin]
 *
 * Ensure settings.php is updated with proper application id and admin secret
 * key. Once that is done, please go through all the arrays here:
 *
 * $facets                  Facet fields.
 *
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
 */


require_once __DIR__ . DIRECTORY_SEPARATOR . '../parse_args.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '../settings.php';

if (isset($argv, $argv[5])) {
  $source_index = $env . '_' . $argv[5];
  $source_app_id = $app_id;
  $source_app_secret_admin = $app_secret_admin;
}

use Algolia\AlgoliaSearch\SearchClient;
$clientSource = SearchClient::create($source_app_id, $source_app_secret_admin);
$client = SearchClient::create($app_id, $app_secret_admin);


$name = $prefix . '_' . $product_list_suffix;
print $name . PHP_EOL;

$source_name = $source_index . '_' . $product_list_suffix;
$indexSource = $clientSource->initIndex($source_name);
$settingsSource = $indexSource->getSettings();
$sourceQueries = algolia_get_query_suggestions($app_id, $app_secret_admin, $source_name);
$sourceQuery = reset($sourceQueries);
$sourceSynonyms = algolia_get_synonyms($indexSource);

$index = $client->initIndex($name);
foreach ($settingsSource['replicas'] as $replica) {
  $replicaIndex = $clientSource->initIndex($replica);
  $settingsSourceReplica[$replica] = $replicaIndex->getSettings();
}
algolia_update_index($client, $index, $settingsSource, $settingsSourceReplica, algolia_get_rules($indexSource));

$queries = algolia_get_query_suggestions($app_id, $app_secret_admin, $name);
$query = reset($queries);
$query['sourceIndices'][0]['facets'] = $sourceQuery['sourceIndices'][0]['facets'];
$query['sourceIndices'][0]['generate'] = $sourceQuery['sourceIndices'][0]['generate'];
algolia_add_query_suggestion($app_id, $app_secret_admin, $query['indexName'], json_encode($query));

// Clear before creating.
$index->clearSynonyms(TRUE);
$index->batchSynonyms($sourceSynonyms, TRUE, TRUE);
