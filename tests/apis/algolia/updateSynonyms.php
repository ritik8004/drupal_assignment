<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Update synonyms for each language.
 */

/**
 * How to use this:
 *
 * Usage: php updateSynonyms.php [brand] [env] [app_id] [app_secret_admin]
 *
 * Ensure settings.php is updated with proper application id and admin secret
 * key. Once that is done, please go through all the arrays here:
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

$brand_code = substr($brand, 0, -2);

use AlgoliaSearch\Client;
$client = new Client($app_id, $app_secret_admin);

foreach ($languages as $language) {
  $name = $prefix . '_' . $language;
  $index = $client->initIndex($name);

  $file = __DIR__ . '/../../../architecture/algolia/synonyms/' . $brand_code . '_' . $language . '.txt';
  $synonyms = file_get_contents($file);
  if (empty($synonyms)) {
    print 'No synonyms found in ' . $file . PHP_EOL;
    continue;
  }

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
}

print PHP_EOL . PHP_EOL;
