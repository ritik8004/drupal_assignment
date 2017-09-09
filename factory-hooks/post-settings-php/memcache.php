<?php
/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// We don't do anything on travis.
if (!getenv('TRAVIS')) {
  // Enable memcache.
  $settings['additional_modules'][] = 'memcache';

  // Default settings.
  $settings['memcache']['extension'] = 'Memcached';
  $settings['memcache']['stampede_protection'] = TRUE;

  // Set default cache backend to memcache.
  $settings['cache']['default'] = 'cache.backend.memcache';

  // Enable pcb_memcache for stock and set it by default.
  $settings['additional_modules'][] = 'pcb_memcache';
  $settings['cache']['bins']['stock'] = 'cache.backend.permanent_memcache';
}
