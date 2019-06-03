<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Update index and it's replicas for each language.
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
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $prefix . '_' . $language;

  $index = $client->initIndex($name);

  $settings = $index->getSettings();
  $settings['attributesForFaceting'] = $facets;
  $settings['searchableAttributes'] = $searchable_attributes;
  $index->setSettings($settings);
  sleep(3);

  foreach ($settings['replicas'] as $replica) {
    $replicaIndex = $client->initIndex($replica);
    $replicaSettings = $replicaIndex->getSettings();
    $replicaSettings['attributesForFaceting'] = $facets;
    $replicaSettings['searchableAttributes'] = $searchable_attributes;
    $replicaIndex->setSettings($replicaSettings);
    sleep(3);
  }

  print $name . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
