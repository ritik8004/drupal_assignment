<?php
// @codingStandardsIgnoreFile
use AlgoliaSearch\Client;

/**
 * @file
 * Custom code to add support for creating query suggestions.
 */

function algolia_get_query_suggestions($app_id, $app_secret_admin, $index) {
  static $result;

  if (empty($result)) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://query-suggestions.fi.algolia.com/1/configs');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = [];
    $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
    $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }

    curl_close($ch);
  }

  $queries = array_filter(json_decode($result, TRUE),
    function ($a) use ($index) {
      $sources = array_column($a['sourceIndices'], 'indexName');
      return in_array($index, $sources);
    }
  );

  return $queries;
}

function algolia_add_query_suggestion($app_id, $app_secret_admin, $name, $data) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://query-suggestions.fi.algolia.com/1/configs/' . $name);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

  curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

  $headers = [];
  $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
  $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch) . PHP_EOL . PHP_EOL;
  }
  else {
    print $result . PHP_EOL . PHP_EOL;
  }
  curl_close($ch);
}


function algolia_delete_query_suggestion($app_id, $app_secret_admin, $index) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://query-suggestions.fi.algolia.com/1/configs/' . $index);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

  $headers = [];
  $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
  $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
  }

  curl_close($ch);
}

function algolia_create_index($app_id, $app_secret_admin, $language, $prefix) {
  global $source_app_id, $source_app_secret_admin, $source_index;
  global $sorts, $facets, $query_facets, $query_generate;
  global $searchable_attributes, $ranking;

  $clientSource = new Client($source_app_id, $source_app_secret_admin);
  $client = new Client($app_id, $app_secret_admin);

  $indexSource = $clientSource->initIndex($source_index . '_' . $language);
  $settingsSource = $indexSource->getSettings();
  $ranking = $settingsSource['ranking'];
  $searchable_attributes = $settingsSource['searchableAttributes'];

  $name = $prefix . '_' . $language;

  // Just need a dummy index to create our index as there is no API to create
  // new index directly.
  $client->copyIndex('dummy', $name);
  $index = $client->initIndex($name);

  $settings = $settingsSource;
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

  // Let index be created properly and crons executed.
  sleep(60);
  algolia_add_query_suggestion($app_id, $app_secret_admin, $query_suggestion, json_encode($query));

  print $name . PHP_EOL;
  print $query_suggestion . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}