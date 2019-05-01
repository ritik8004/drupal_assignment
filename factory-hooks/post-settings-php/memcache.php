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
    $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
    $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
    $settings['cache']['bins']['config'] = 'cache.backend.memcache';

    // Use memcache as the default bin.
    $settings['cache']['default'] = 'cache.backend.memcache';

    // Use pcb_memcache for stock.
    $settings['cache']['bins']['stock'] = 'cache.backend.permanent_memcache';
    // Use pcb_memcache for category tree.
    $settings['cache']['bins']['product_category_tree'] = 'cache.backend.permanent_memcache';
    // Use pcb_memcache for product options.
    $settings['cache']['bins']['product_options'] = 'cache.backend.permanent_memcache';

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

    // Optionally set up chainedfast backend to measure performance difference.
    // The purpose is to measure performance difference between using memcache
    // and chainedfast backend for bootstrap, discovery and config cache bins.
    // Default setting is memcache backend.
    //
    // To switch backend to chainedfast, create/edit the /home/alshaya/includes
    // /memcache-settings.php file and enter
    // $_ENV[<host>]['MEMCACHE_CHAINEDFAST_ENABLE'] = 1;
    // (e.g. $_ENV['bbkw.uat-alshaya.acsitefactory.com']['MEMCACHE_CHAINEDFAST_ENABLE'] = 1;
    // to enable chanedfast backend instead of memcache.
    if (file_exists('/home/alshaya/includes/memcache-settings.php')) {
      include_once '/home/alshaya/includes/memcache-settings.php';

      if (!empty($_ENV[$_ENV['HTTP_HOST']]['MEMCACHE_CHAINEDFAST_ENABLE'])) {
        $settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
        $settings['cache']['bins']['discovery'] = 'cache.backend.chainedfast';
        $settings['cache']['bins']['config'] = 'cache.backend.chainedfast';
      }
    }
  }
}
