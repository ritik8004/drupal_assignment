<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['middleware_auth'] = '5um6y5nxl3oqms9qw0jai36qkryrrocg';

// v1 = backend is middleware, i.e. we call middleware APIs to perform commerce
// operations.
// v2 = backend is magento, i.e. we call magento APIs directly to perform
// commerce operations.
$settings['commerce_backend']['version'] = 'v1';
