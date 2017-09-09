<?php
/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['memcache']['extension'] = 'Memcached';
$settings['memcache']['stampede_protection'] = TRUE;
$settings['cache']['bins']['stock'] = 'cache.backend.permanent_memcache';

if ($is_ah_env) {
  $settings['cache']['default'] = 'cache.backend.memcache';
}
