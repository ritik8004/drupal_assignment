<?php
/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Default settings.
$settings['memcache']['extension'] = 'Memcached';
$settings['memcache']['stampede_protection'] = TRUE;

if (isset($settings, $settings['env']) && $settings['env'] == 'local') {
  $hostname = $_SERVER['HTTP_HOST'];
  $hostname_parts = explode('.', $hostname);

  $settings['memcache']['key_prefix'] = str_replace('-', '', $hostname_parts[1]);
}

// Set default cache backend to memcache.
$settings['cache']['default'] = 'cache.backend.memcache';

$class_loader->addPsr4('Drupal\\memcache\\', 'modules/contrib/memcache/src');

// Define custom bootstrap container definition to use Memcache for cache.container.
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
    'memcache.config' => [
      'class' => 'Drupal\memcache\DrupalMemcacheConfig',
      'arguments' => ['@settings'],
    ],
    'memcache.backend.cache.factory' => [
      'class' => 'Drupal\memcache\DrupalMemcacheFactory',
      'arguments' => ['@memcache.config']
    ],
    'memcache.backend.cache.container' => [
      'class' => 'Drupal\memcache\DrupalMemcacheFactory',
      'factory' => ['@memcache.backend.cache.factory', 'get'],
      'arguments' => ['container'],
    ],
    'lock.container' => [
      'class' => 'Drupal\memcache\Lock\MemcacheLockBackend',
      'arguments' => ['container', '@memcache.backend.cache.container'],
    ],
    'cache_tags_provider.container' => [
      'class' => 'Drupal\Core\Cache\DatabaseCacheTagsChecksum',
      'arguments' => ['@database'],
    ],
    'cache.container' => [
      'class' => 'Drupal\memcache\MemcacheBackend',
      'arguments' => ['container', '@memcache.backend.cache.container', '@lock.container', '@memcache.config', '@cache_tags_provider.container'],
    ],
  ],
];

// Use pcb_memcache for stock.
$settings['cache']['bins']['stock'] = 'cache.backend.permanent_memcache';
// Use pcb_memcache for category tree.
$settings['cache']['bins']['product_category_tree'] = 'cache.backend.permanent_memcache';
// Use pcb_memcache for product options.
$settings['cache']['bins']['product_options'] = 'cache.backend.permanent_memcache';

// Fix for PHP 7.1, see https://backlog.acquia.com/browse/PF-1118.
$settings['memcache']['options'] = [
  Memcached::OPT_COMPRESSION => TRUE,
];
