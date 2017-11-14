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

// Set default cache backend to memcache.
$settings['cache']['default'] = 'cache.backend.memcache';

// Use pcb_memcache for stock.
$settings['cache']['bins']['stock'] = 'cache.backend.permanent_memcache';

// Use permanent_database for labels, we don't want to loose mappings
// when server restarts.
$settings['cache']['bins']['product_labels'] = 'cache.backend.permanent_database';
