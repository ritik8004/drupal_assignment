<?php
// @codingStandardsIgnoreFile

use Algolia\AlgoliaSearch\SearchClient;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

$app_id = 'testing24192T8KHZ';
$app_secret_admin = '81c93293993d87fb67f2af22749ecbeb';

$brands = [
  'mckw',
  'mcsa',
  'mcae',
  'hmkw',
  'hmsa',
  'hmae',
  'hmeg',
];

$envs = [
  'local',
  '01dev',
  '01dev2',
  '01dev3',
  '01qa2',
  '01test',
];

global $languages;

$client = SearchClient::create($app_id, $app_secret_admin);

foreach ($envs as $env) {
  foreach ($brands as $brand) {
    $prefix = $env . '_' . $brand;
    foreach ($languages as $language) {
      $name = $prefix . '_' . $language;
      $query_suggestion = $name . '_query';

      try {
        $querySuggestions = algolia_get_query_suggestions($app_id, $app_secret_admin, $name);
        if (!empty($querySuggestions)) {
          print 'Skipping creation of query suggestion for: ' . $name . PHP_EOL;
          continue;
        }
      }
      catch (\Exception $e) {
        // Do nothing.
      }

      $index = $client->initIndex($name);
      $settings = $index->getSettings();

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

      print 'Creating query suggestion for: ' . $name . PHP_EOL;
      algolia_add_query_suggestion($app_id, $app_secret_admin, $query_suggestion, json_encode($query));
      print PHP_EOL . PHP_EOL;
    }
  }
}
