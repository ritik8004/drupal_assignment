<?php
// @codingStandardsIgnoreFile
use AlgoliaSearch\Client;

/**
 * @file
 * Custom code to add support for creating query suggestions.
 */

define('ALGOLIA_QUERY_SUGGESTTIONS_URL', 'https://query-suggestions.eu.algolia.com/1/configs');

function algolia_get_query_suggestions($app_id, $app_secret_admin, $index) {
  static $result;

  if (empty($result[$app_id])) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, ALGOLIA_QUERY_SUGGESTTIONS_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = [];
    $headers[] = 'X-Algolia-Api-Key: ' . $app_secret_admin;
    $headers[] = 'X-Algolia-Application-Id: ' . $app_id;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result[$app_id] = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }

    curl_close($ch);
  }

  $queries = array_filter(json_decode($result[$app_id], TRUE),
    function ($a) use ($index) {
      $sources = array_column($a['sourceIndices'], 'indexName');
      return in_array($index, $sources);
    }
  );

  return $queries;
}

function algolia_add_query_suggestion($app_id, $app_secret_admin, $name, $data) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, ALGOLIA_QUERY_SUGGESTTIONS_URL . '/' . $name);
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

  curl_setopt($ch, CURLOPT_URL, ALGOLIA_QUERY_SUGGESTTIONS_URL . '/' . $index);
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
  unset($settings['replicas']);

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

function algolia_update_synonyms($app_id, $app_secret_admin, $language, $env, $brand) {
  $brand_code = substr($brand, 0, -2);
  $file = __DIR__ . '/../../../architecture/algolia/synonyms/' . $brand_code . '_' . $language . '.txt';
  $synonyms = file_get_contents($file);
  if (empty($synonyms)) {
    print 'No synonyms found in ' . $file . PHP_EOL;
    return;
  }

  $client = new Client($app_id, $app_secret_admin);
  $client->setConnectTimeout(3000, 3000);
  $name = $env . '_' . $brand . '_' . $language;
  $index = $client->initIndex($name);

  $synonyms = explode(PHP_EOL, $synonyms);
  foreach ($synonyms as $synonym) {
    $values = explode(',', $synonym);
    $key = 'syn_' . $values[0];

    $content = [
      'type' => 'synonym',
      'synonyms' => $values,
      'objectID' => $key,
    ];
    $index->saveSynonym($key, $content, TRUE);
  }

  print 'Synonyms saved for: ' . $name . PHP_EOL;
}

function algolia_get_synonyms(\AlgoliaSearch\Index $index) {
  $page = 0;
  $synonyms = [];

  do {
    $synonymsPage = $index->searchSynonyms(NULL, [], $page, 500);

    if (empty($synonymsPage['hits'])) {
      break;
    }

    $synonyms = array_merge($synonyms, $synonymsPage['hits']);

    $page++;
    $total = $synonymsPage['nbPages'] ?? 0;
    if ($page >= $total) {
      break;
    }
  } while(1);

  if ($synonyms) {
    foreach ($synonyms as &$synonym) {
      unset($synonym['_highlightResult']);
    }
  }

  return $synonyms;
}

function algolia_get_rules($indexSource) {
  $page = 0;
  $rules = [];
  do {
    $rulesPage = $indexSource->searchRules(['page' => $page]);
    if (empty($rulesPage['hits'])) {
      break;
    }

    $rules = array_merge($rules, $rulesPage['hits']);

    $page++;
    if ($page >= $rulesPage['nbPages']) {
      break;
    }
  } while(1);

  if ($rules) {
    foreach ($rules as &$rule) {
      unset($rule['_highlightResult']);
    }
  }

  return $rules;
}

function algolia_save_rules($index, $rules) {
  if (is_array($rules) && !empty($rules)) {
    $index->batchRules($rules, TRUE, TRUE);
  }
}

function algolia_update_index($client, $index, $settingsSource, $rules) {
  $settings = $index->getSettings();
  $settingsSource['replicas'] = $settings['replicas'];
  $index->setSettings($settingsSource);
  sleep(1);

  unset($settingsSource['replicas']);

  foreach ($settings['replicas'] as $replica) {
    $replicaIndex = $client->initIndex($replica);
    $replicaSettings = $replicaIndex->getSettings();
    $settingsSource['ranking'] = $replicaSettings['ranking'];
    $replicaIndex->setSettings($settingsSource);
    sleep(1);
  }

  algolia_save_rules($index, $rules);
}
