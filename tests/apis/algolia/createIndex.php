<?php

/**
 * @file
 * Create index and it's replicas for each language.
 */

$env = isset($argv, $argv[1]) ? $argv[1] : '';
if (empty($env)) {
  print 'No env passed as parameter.' . PHP_EOL . PHP_EOL;
  die();
}

require_once __DIR__ . '/../../../vendor/autoload.php';

use AlgoliaSearch\Client;

$languages = [
  'en',
  'ar',
];

$sorts = [
  [
    'field' => 'title',
    'direction' => 'asc',
  ],
  [
    'field' => 'title',
    'direction' => 'desc',
  ],
  [
    'field' => 'final_price',
    'direction' => 'asc',
  ],
  [
    'field' => 'final_price',
    'direction' => 'desc',
  ],
  [
    'field' => 'created',
    'direction' => 'desc',
  ],
  [
    'field' => 'search_api_relevance',
    'direction' => 'desc',
  ],
];

$searchable_attributes = [
  'title',
  'field_category_name',
  'attr_product_brand',
  'sku',
  'attr_product_collection',
  'attr_concept',
  'attr_color',
  'attr_size',
  'body',
];

$facets = [
  'attr_color',
  'attr_product_brand',
  'attr_selling_price',
  'attr_size',
  'field_acq_promotion_label',
  'field_category',
  'final_price',
  'attr_product_collection',
  'attr_concept',
  'attr_selling_price',
];

$ranking = [
  'desc(stock)',
  'words',
  'filters',
  'exact',
  'custom',
];

$client = new Client('HGR051I5XN', '6fc229a5d5d0f0d9cc927184b2e4af3f');

foreach ($languages as $language) {
  $name = $env . '_' . $language;
  // Just need a dummy index to create our index as there is no API to create
  // new index directly.
  $client->copyIndex('local_en', $name);
  $index = $client->initIndex($name);
  sleep(10);

  $settings = $index->getSettings();
  $settings['attributesForFaceting'] = $facets;
  $settings['searchableAttributes'] = $searchable_attributes;
  $settings['ranking'] = $ranking;
  $index->setSettings($settings, TRUE);

  foreach ($sorts as $sort) {
    $replica = $name . '_' . implode('_', $sort);
    $settings['replicas'][] = $replica;
    $client->copyIndex($name, $replica);
  }

  sleep(2);
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

  print $name . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
