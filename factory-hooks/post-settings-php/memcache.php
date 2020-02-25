<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Factory hook implementation for memcache.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

use Composer\Autoload\ClassLoader;

// Check for PHP Memcached libraries.
$memcache_exists = class_exists('Memcache', FALSE);
$memcached_exists = class_exists('Memcached', FALSE);
$memcache_module_is_present = file_exists(DRUPAL_ROOT . '/modules/contrib/memcache/memcache.services.yml');
if ($memcache_module_is_present && ($memcache_exists || $memcached_exists)) {
  // Use Memcached extension if available.
  if ($memcached_exists) {
    $settings['memcache']['extension'] = 'Memcached';
  }

  if (class_exists(ClassLoader::class)) {
    $class_loader = new ClassLoader();
    $class_loader->addPsr4('Drupal\\memcache\\', DRUPAL_ROOT . '/modules/contrib/memcache/src');
    $class_loader->register();

    $settings['container_yamls'][] = DRUPAL_ROOT . '/modules/contrib/memcache/memcache.services.yml';

    // Define custom bootstrap container definition to use Memcache for
    // cache.container.
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'database' => [
          'class' => 'Drupal\Core\Database\Connection',
          'factory' => 'Drupal\Core\Database\Database::getConnection',
          'arguments' => ['default'],
        ],
        'settings' => [
          'class' => 'Drupal\Core\Site\Settings',
          'factory' => 'Drupal\Core\Site\Settings::getInstance',
        ],
        'memcache.settings' => [
          'class' => 'Drupal\memcache\MemcacheSettings',
          'arguments' => ['@settings'],
        ],
        'memcache.backend.cache.factory' => [
          'class' => 'Drupal\memcache\Driver\MemcacheDriverFactory',
          'arguments' => ['@memcache.settings'],
        ],
        'memcache.backend.cache.container' => [
          'class' => 'Drupal\memcache\DrupalMemcacheFactory',
          'factory' => ['@memcache.backend.cache.factory', 'get'],
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\Core\Cache\DatabaseCacheTagsChecksum',
          'arguments' => ['@database'],
        ],
        'cache.container' => [
          'class' => 'Drupal\memcache\MemcacheBackend',
          'arguments' => [
            'container',
            '@memcache.backend.cache.container',
            '@cache_tags_provider.container',
          ],
        ],
      ],
    ];

    // Override default fastchained backend for static bins.
    // @see https://www.drupal.org/node/2754947
    // We have a way of setting values in $settings from include
    // files per brand, we might set it to different value from
    // there. To avoid overriding we have added this check.
    // @see factory-hooks/post-settings-php/includes.php.
    if (!isset($settings['cache']['bins'])) {
      $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
      $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
      $settings['cache']['bins']['config'] = 'cache.backend.memcache';
    }

    // Use memcache as the default bin.
    $settings['cache']['default'] = 'cache.backend.memcache';

    $settings['cache']['bins']['product_category_tree'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['product_options'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['alshaya_product_configurations'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['orders_count'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['product_processed_data'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['cart_history'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['google_tag'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['alshaya_acm_promotion'] = 'cache.backend.permanent_memcache';
    $settings['cache']['bins']['pretty_paths'] = 'cache.backend.permanent_memcache';

    // Use database for some, we replace heavy queries with simple one here.
    // $settings['cache']['bins']['pretty_paths'] = 'cache.backend.permanent_database';

    // Enable stampede protection.
    $settings['memcache']['stampede_protection'] = TRUE;

    // Fix for PHP 7.1, see https://backlog.acquia.com/browse/PF-1118.
    $settings['memcache']['options'] = [
      Memcached::OPT_COMPRESSION => TRUE,
    ];

    if (isset($settings, $settings['env']) && $settings['env'] == 'local') {
      global $host_site_code;
      $settings['memcache']['key_prefix'] = $host_site_code;
    }
  }
}
