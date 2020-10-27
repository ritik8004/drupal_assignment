<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['alshaya_backend_calls_options'] = [
  'magento' => [
    'place_order' => [
      'timeout' => 60,
    ],
    'update_cart' => [
      'timeout' => 60,
    ],
    'get_cart' => [
      'timeout' => 60,
    ],
    'get_cart_for_checkout' => [
      'timeout' => 60,
    ],
  ],
];
