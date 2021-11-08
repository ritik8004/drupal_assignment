<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Create replica for sort by changed field.
 */


use Algolia\AlgoliaSearch\SearchClient;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'prod_keys.php';

global $languages;

$requested_brand = isset($argv, $argv[1]) ? $argv[1] : '';

foreach ($all_sites as $brand => $secret) {
  if ($requested_brand && $brand !== $requested_brand) {
    continue;
  }

  sleep(1);
  $client = SearchClient::create($secret['app_id'], $secret['app_secret']);
  foreach ($client->listIndices()['items'] as $index_info) {
    // Skip the replicas.
    if (isset($index_info['primary'])) {
      continue;
    }

    // Do not execute on query indexes.
    if (strpos($index_info['name'], 'query') !== FALSE) {
      continue;
    }

    // We want to perform this only on prod.
    if (strpos($index_info['name'], '01live_') === FALSE) {
      continue;
    }

    // Process only for listing page indexes.
    if (strpos($index_info['name'], 'product_list') === FALSE) {
      continue;
    }

    sleep(1);

    $replica_name = $index_info['name'] . '_changed_asc';

    $index = $client->initIndex($index_info['name']);
    $settings = $index->getSettings();

    if (empty($settings['replicas'])) {
      print 'Replicas empty in main index ' . $index_info['name'] . '.' . PHP_EOL;
      continue;
    }

    if (in_array($replica_name, $settings['replicas'])) {
      continue;
    }

    print 'Processing for : '. $replica_name . PHP_EOL;

    if (!in_array($replica_name, $settings['replicas'])) {
      $settings['replicas'][] = $replica_name;
      $response = $index->setSettings($settings, ['forwardToReplicas' => FALSE]);
      $response->wait();
    }

    $ranking = $settings['ranking'];
    try {
      $replica = $client->initIndex($replica_name);
      $replica_settings = $replica->getSettings();
      $replica_settings['ranking'] = [
          'asc(changed.en)',
        ] + $ranking;
      $response = $replica->setSettings($replica_settings);
      $response->wait();
      break;
    }
    catch(\Exception $e) {
      print $replica_name . ' : ' . $e->getMessage() . PHP_EOL;
    }
  }
}
