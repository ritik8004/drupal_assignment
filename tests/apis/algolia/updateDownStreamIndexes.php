<?php
// phpcs:ignoreFile

require_once __DIR__ . '/../../../factory-hooks/environments/settings.php';

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

$brand_code = substr($brand, 0, -2);
$market = substr($brand, -2);

$system_settings = alshaya_get_additional_settings($brand_code, $market, 'dev');

$sandbox_app_id = $system_settings['algolia_sandbox.settings']['app_id'];
$sandbox_app_secret_admin = $system_settings['algolia_sandbox.settings']['write_api_key'];

if ($env != '01live') {
  print 'We should copy to downstream indexes only from production.';
  print PHP_EOL . PHP_EOL . PHP_EOL;
  die();
}

use Algolia\AlgoliaSearch\SearchClient;

$clientSource = SearchClient::create($app_id, $app_secret_admin);
$sandboxClient = SearchClient::create($sandbox_app_id, $sandbox_app_secret_admin);
$client = SearchClient::create($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $source_name = '01live_' . $brand . '_' . $language;
  $indexSource[$language] = $clientSource->initIndex($source_name);
  $settingsSource[$language] = $indexSource[$language]->getSettings();

  foreach ($settingsSource[$language]['replicas'] as $replica) {
    $replicaIndex = $clientSource->initIndex($replica);
    $settingsSourceReplica[$language][$replica] = $replicaIndex->getSettings();
  }

  $rules[$language] = algolia_get_rules($indexSource[$language]);
  $sourceSynonyms[$language] = algolia_get_synonyms($indexSource[$language]);
}

foreach ($sandbox_envs as $sandbox_env) {
  foreach ($languages as $language) {
    try {
      $name = $sandbox_env . '_' . $brand . '_' . $language;
      print $name . PHP_EOL;
      $index = $sandboxClient->initIndex($name);
      algolia_update_index($sandboxClient, $index, $settingsSource[$language], $settingsSourceReplica[$language], $rules[$language]);

      // Clear before creating.
      $index->clearSynonyms(TRUE);
      $index->batchSynonyms($sourceSynonyms[$language], TRUE, TRUE);
    }
    catch (\Exception $e) {
      print 'Exception occurred for ' . $name . ':' . $e->getMessage() . PHP_EOL . PHP_EOL;
    }
  }
}

foreach ($prod_envs as $prod_env) {
  foreach ($languages as $language) {
    try {
      $name = $prod_env . '_' . $brand . '_' . $language;
      print $name . PHP_EOL;
      $index = $client->initIndex($name);
      algolia_update_index($client, $index, $settingsSource[$language], $settingsSourceReplica[$language], $rules[$language]);

      // Clear before creating.
      $index->clearSynonyms(TRUE);
      $index->batchSynonyms($sourceSynonyms[$language], TRUE, TRUE);
    }
    catch (\Exception $e) {
      print 'Exception occurred for ' . $name . ':' . $e->getMessage() . PHP_EOL . PHP_EOL;
    }
  }
}
