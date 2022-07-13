<?php
// phpcs:ignoreFile

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

foreach ($languages as $language) {
  algolia_update_synonyms($app_id, $app_secret_admin, $language, $env, $brand);
}

print PHP_EOL . PHP_EOL;
