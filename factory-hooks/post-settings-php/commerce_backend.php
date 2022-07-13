<?php
// phpcs:ignoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$env = 'local';

$ah_env = getenv('AH_SITE_ENVIRONMENT');
if ($ah_env && $ah_env !== 'ide') {
  $env = $ah_env;
}

$settings['middleware_auth'] = '5um6y5nxl3oqms9qw0jai36qkryrrocg';

// 1 = backend is middleware, i.e. we call middleware APIs to perform commerce
// operations.
// 2 = backend is magento, i.e. we call magento APIs directly to perform
// commerce operations.
$settings['commerce_backend']['version'] = 2;

// Use this setting to toggle blocking and unblocking of calls to middleware
// after making the switch to V2.
$settings['commerce_backend']['block_middleware'] = 1;
