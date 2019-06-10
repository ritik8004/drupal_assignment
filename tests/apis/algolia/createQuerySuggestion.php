<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Create query suggestions for each language for an index.
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
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
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
  sleep(60);

  print $query_suggestion . PHP_EOL;
  print PHP_EOL . PHP_EOL . PHP_EOL;
}
