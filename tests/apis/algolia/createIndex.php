<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Create index and it's replicas for each language.
 */

/**
 * How to use this:
 *
 * Usage: php createIndex.php [prefix]
 * Example: php createIndex.php mckw_01dev
 *
 * Ensure settings.php is updated with proper application id and admin secret
 * key. Once that is done, please go through all the arrays here:
 *
 * $languages:              Specify all the languages for which primary indexes
 *                          need to be created.
 *
 * $sorts:                  Replicas need to be created for each sorting option
 *                          required by Views
 *
 * $searchable_attributes   Attributes that should be used for searching.
 *
 * $facets                  Facet fields.
 *
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
 *
 * $ranking:                Default ranking.
 */


require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

use AlgoliaSearch\Client;

$clientSource = new Client($source_app_id, $source_app_secret_admin);
$indexSource = $clientSource->initIndex($source_index);
$settingsSource = $indexSource->getSettings();
$ranking = $settingsSource['ranking'];

$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $prefix . '_' . $language;

  // Just need a dummy index to create our index as there is no API to create
  // new index directly.
  $client->copyIndex('dummy', $name);

  do {
    try {
      $index = $client->initIndex($name);
      sleep(10);
      $settings = $index->getSettings();
    }
    catch (\Exception $e) {
      print $e->getMessage() . PHP_EOL;
    }
  } while (!isset($settings));
  print PHP_EOL . PHP_EOL;

  $settings = array_merge($settingsSource, $settings);
  $settings['attributesForFaceting'] = $facets;
  $settings['searchableAttributes'] = $searchable_attributes;
  $settings['ranking'] = $ranking;
  $index->setSettings($settings, TRUE);

  foreach ($sorts as $sort) {
    $replica = $name . '_' . implode('_', $sort);
    $settings['replicas'][] = $replica;
    $client->copyIndex($name, $replica);
  }
  sleep(3);

  $index->setSettings($settings, TRUE);

  foreach ($sorts as $sort) {
    $replica = $name . '_' . implode('_', $sort);
    $replica_index = $client->initIndex($replica);
    $replica_settings = $replica_index->getSettings();
    $replica_settings['ranking'] = [
      'desc(stock)',
      $sort['direction'] . '(' . $sort['field'] . ')',
    ] + $ranking;
    $replica_index->setSettings($replica_settings);
  }

  $query_suggestion = $name . '_query';
  $query = [
    'indexName' => $query_suggestion,
    'sourceIndices' => [
      [
        'indexName' => $name,
        'facets' => $query_facets,
        'generate' => $query_generate,
      ],
    ],
  ];

  foreach ($settings['replicas'] as $replica) {
    $query['sourceIndices'][] = [
      'indexName' => $replica,
    ];
  }

  algolia_add_query_suggestion($app_id, $app_secret_admin, $query_suggestion, json_encode($query));
  sleep(30);

  print $name . PHP_EOL;
  print $query_suggestion . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
