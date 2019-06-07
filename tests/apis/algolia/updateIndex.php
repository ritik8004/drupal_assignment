<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Update index and it's replicas for each language.
 */

/**
 * How to use this:
 *
 * Usage: php createIndex.php [brand] [env] [app_id] [app_secret_admin]
 *
 * Ensure settings.php is updated with proper application id and admin secret
 * key. Once that is done, please go through all the arrays here:
 *
 * $languages:              Specify all the languages for which primary indexes
 *                          need to be created.
 *
 * $facets                  Facet fields.
 *
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
 */


require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

use AlgoliaSearch\Client;
$clientSource = new Client($source_app_id, $source_app_secret_admin);
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $indexSource = $clientSource->initIndex($source_index . '_' . $language);
  $settingsSource = $indexSource->getSettings();
  $name = $prefix . '_' . $language;
  $index = $client->initIndex($name);

  $settings = $index->getSettings();
  $settingsSource['replicas'] = $settings['replicas'];
  $index->setSettings($settingsSource);
  sleep(3);

  unset($settingsSource['replicas']);

  foreach ($settings['replicas'] as $replica) {
    $replicaIndex = $client->initIndex($replica);
    $replicaSettings = $replicaIndex->getSettings();
    $settingsSource['ranking'] = $replicaSettings['ranking'];
    $replicaIndex->setSettings($settingsSource);
    sleep(3);
  }

  print $name . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
