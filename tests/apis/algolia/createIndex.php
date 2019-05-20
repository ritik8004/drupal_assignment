<?php
// @codingStandardsIgnoreFile

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
];

$query_facets = [
  [
    'attribute' => 'field_category_name',
    'amount' => 1,
  ],
];

$query_generate = [
  ['field_acq_promotion_label'],
  ['attr_product_brand'],
  ['attr_product_collection'],
  ['attr_concept'],
  ['attr_color'],
];

$ranking = [
  'desc(stock)',
  'words',
  'filters',
  'exact',
  'custom',
];

require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'custom.php';
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $env . '_' . $language;

  // Just need a dummy index to create our index as there is no API to create
  // new index directly.
  $client->copyIndex('local_en', $name);
  $index = $client->initIndex($name);
  sleep(10);

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
  algolia_add_query_suggestion($app_id, $app_secret_admin, $query_suggestion, json_encode($query));

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
  print $query_suggestion . PHP_EOL;
  print implode(PHP_EOL, $settings['replicas']);
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
