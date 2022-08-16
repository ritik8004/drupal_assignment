<?php
// phpcs:ignoreFile
use Algolia\AlgoliaSearch\SearchClient;

/**
 * @file
 * Custom code to add support for creating query suggestions.
 */

function algolia_create_index($app_id, $app_secret_admin, $language, $prefix) {
  global $source_app_id, $source_app_secret_admin, $source_index;
  global $sorts, $facets;
  global $searchable_attributes, $ranking;
  global $migrate_index;
  global $product_list_suffix;
  global $languages;
  global $attributes_skip_lang_suffix;

  $clientSource = SearchClient::create($source_app_id, $source_app_secret_admin);
  $client = SearchClient::create($app_id, $app_secret_admin);

  $indexSource = $clientSource->initIndex($source_index . '_product_list');

  $settingsSource = $indexSource->getSettings();
  $ranking = $settingsSource['ranking'];

  // We create product list index.
  $name = $prefix . '_' . $product_list_suffix;

  // Just need a dummy index to create our index as there is no API to create
  // new index directly.
  $index = $client->initIndex($name);

  $settings = $settingsSource;

  $settings['ranking'] = $ranking;
  unset($settings['replicas']);

  if ($migrate_index) {
    // Add language suffix to searchable attributes.
    foreach ($settingsSource['searchableAttributes'] as $searchableAttribute) {
      // Check If attribute does not require language suffix.
      // Get attribute between round parenthesis.
      preg_match('#\((.*?)\)#', $searchableAttribute, $match);
      if (in_array($match[1], $attributes_skip_lang_suffix)) {
        $searchableAttributes[] = $searchableAttribute;
        continue;
      }
      foreach ($languages as $lang_code) {
        if (strstr($searchableAttribute, '.')) {
          $searchableAttributes[] = str_replace('.', '.' . $lang_code . '.', $searchableAttribute);
        }
        else {
          $searchableAttributes[] = str_replace(')', '.' . $lang_code . ')', $searchableAttribute);
        }
      }
    }
    $settings['searchableAttributes'] = $searchableAttributes;

    foreach ($settingsSource['attributesForFaceting'] as $facetAttribute) {
      $facetAttributes[] = $facetAttribute;
    }
    $settings['attributesForFaceting'] = $facetAttributes;
  }
  else {
    $settings['searchableAttributes'] = $searchable_attributes;
    $settings['attributesForFaceting'] = $facets;
  }

  $index->setSettings($settings, ['forwardToReplicas' => TRUE,])->wait();

  foreach ($sorts as $sort) {
    foreach ($languages as $lang_code) {
      $replica = $name . '_' . $lang_code . '_' . implode('_', $sort);
      $settings['replicas'][] = $replica;
    }
  }

  $index->setSettings($settings, ['forwardToReplicas' => TRUE,])->wait();

  foreach ($sorts as $sort) {
    foreach ($languages as $lang_code) {
      $replica = $name . '_' . $lang_code . '_' . implode('_', $sort);
      $replica_index = $client->initIndex($replica);
      $replica_settings = $replica_index->getSettings();
      $replica_settings['ranking'] = [
          'desc(stock)',
          $sort['direction'] . '(' . $sort['field'] . '.' . $lang_code . ')',
        ] + $ranking;
      $replica_index->setSettings($replica_settings)->wait();
    }
  }

  print $name . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}

function algolia_update_synonyms($app_id, $app_secret_admin, $language, $env, $brand) {
  global $product_list_suffix;
  $brand_code = substr($brand, 0, -2);

  // Use brandcode_synonyms.txt example bbwae_synonyms.txt.
  $file = __DIR__ . '/../../../architecture/algolia/synonyms/' . $brand_code . '_synonyms.txt';

  $synonyms = file_get_contents($file);
  if (empty($synonyms)) {
    print 'No synonyms found in ' . $file . PHP_EOL;
    return;
  }

  $client = SearchClient::create($app_id, $app_secret_admin);
  $client->setConnectTimeout(3000, 3000);

  // Updaate product list index.
  $name = $env . '_' . $brand . '_' . $product_list_suffix;

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

function algolia_get_synonyms(\Algolia\AlgoliaSearch\SearchIndex $index) {
  $page = 0;
  $synonyms = [];

  do {
    $synonymsPage = $index->searchSynonyms(NULL, []);

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

function algolia_update_index($client, $index, $settingsSource, $settingsSourceReplica, $rules) {
  $settings = $index->getSettings();
  $settingsSource['replicas'] = $settings['replicas'];

  $index->setSettings($settingsSource)->wait();

  unset($settingsSource['replicas']);

  foreach ($settings['replicas'] as $replica) {
    $exploded_replica = explode('_', $replica);
    array_shift($exploded_replica);
    array_unshift($exploded_replica, '01live');
    $replica_key = implode('_', $exploded_replica);

    print $replica . PHP_EOL;

    $replicaIndex = $client->initIndex($replica);

    $replicaIndex->setSettings($settingsSourceReplica[$replica_key])->wait();
  }

  algolia_save_rules($index, $rules);
}
