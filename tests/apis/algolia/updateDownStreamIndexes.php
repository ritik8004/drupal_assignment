<?php
// @codingStandardsIgnoreFile

// Sandbox
$sandbox_app_id = 'testing24192T8KHZ';
$sandbox_app_secret_admin = '81c93293993d87fb67f2af22749ecbeb';

$sandbox_envs = [
  'local',
  '01dev',
  '01dev2',
  '01dev3',
  '01qa2',
];

$prod_envs = [
  '01test',
  '01uat',
  '01pprod',
];

global $languages;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

if ($env != '01live') {
  print 'We should copy to downstream indexes only from production.';
  print PHP_EOL . PHP_EOL . PHP_EOL;
  die();
}

use AlgoliaSearch\Client;

$clientSource = new Client($app_id, $app_secret_admin);
$sandboxClient = new Client($sandbox_app_id, $sandbox_app_secret_admin);
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $source_name = '01live_' . $brand . '_' . $language;
  $indexSource[$language] = $clientSource->initIndex($source_name);
  $settingsSource[$language] = $indexSource[$language]->getSettings();
  $rules[$language] = algolia_get_rules($indexSource[$language]);
  $sourceQueries = algolia_get_query_suggestions($app_id, $app_secret_admin, $source_name);
  $sourceQuery[$language] = reset($sourceQueries);
  $sourceSynonyms[$language] = algolia_get_synonyms($indexSource[$language]);
}

foreach ($sandbox_envs as $sandbox_env) {
  foreach ($languages as $language) {
    $name = $sandbox_env . '_' . $brand . '_' . $language;
    print $name . PHP_EOL;
    $index = $sandboxClient->initIndex($name);
    algolia_update_index($sandboxClient, $index, $settingsSource[$language], $rules[$language]);

    $queries = algolia_get_query_suggestions($sandbox_app_id, $sandbox_app_secret_admin, $name);
    $query = reset($queries);
    $query['sourceIndices'][0]['facets'] = $sourceQuery[$language]['sourceIndices'][0]['facets'];
    $query['sourceIndices'][0]['generate'] = $sourceQuery[$language]['sourceIndices'][0]['generate'];
    algolia_add_query_suggestion($sandbox_app_id, $sandbox_app_secret_admin, $query['indexName'], json_encode($query));
    // Clear before creating.
    $index->clearSynonyms(TRUE);
    $index->batchSynonyms($sourceSynonyms[$language], TRUE, TRUE);
  }
}

foreach ($prod_envs as $prod_env) {
  foreach ($languages as $language) {
    $name = $prod_env . '_' . $brand . '_' . $language;
    print $name . PHP_EOL;
    $index = $client->initIndex($name);
    algolia_update_index($client, $index, $settingsSource[$language], $rules[$language]);

    $queries = algolia_get_query_suggestions($app_id, $app_secret_admin, $name);
    $query = reset($queries);
    $query['sourceIndices'][0]['facets'] = $sourceQuery[$language]['sourceIndices'][0]['facets'];
    $query['sourceIndices'][0]['generate'] = $sourceQuery[$language]['sourceIndices'][0]['generate'];
    algolia_add_query_suggestion($app_id, $app_secret_admin, $query['indexName'], json_encode($query));
    // Clear before creating.
    $index->clearSynonyms(TRUE);
    $index->batchSynonyms($sourceSynonyms[$language], TRUE, TRUE);
  }
}
