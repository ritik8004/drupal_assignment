<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Create query suggestions for each language for an index.
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
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
 */


$prefix = isset($argv, $argv[1]) ? $argv[1] : '';
if (empty($prefix)) {
  print 'No prefix passed as parameter.' . PHP_EOL . PHP_EOL;
  die();
}

require_once __DIR__ . '/../../../vendor/autoload.php';

use AlgoliaSearch\Client;

$languages = [
  'en',
  'ar',
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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $prefix . '_' . $language;

  $index = $client->initIndex($name);
  $settings = $index->getSettings();

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

  print $query_suggestion . PHP_EOL;
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
