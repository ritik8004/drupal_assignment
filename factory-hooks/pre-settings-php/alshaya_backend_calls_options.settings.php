<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// "middleware" indicates the source from where the API call is being made.
$settings['alshaya_backend_calls_options'] = [
  'middleware' => [
    'order_place' => [
      'timeout' => 64,
    ],
    'cart_update' => [
      'timeout' => 12,
    ],
    'cart_get' => [
      'timeout' => 7,
    ],
    'cart_create' => [
      'timeout' => 5,
    ],
    'cart_associate' => [
      'timeout' => 11,
    ],
    'cart_estimate_shipping' => [
      'timeout' => 11,
    ],
    'cart_payment_methods' => [
      'timeout' => 7,
    ],
    'cart_selected_payment' => [
      'timeout' => 6,
    ],
    'cart_search' => [
      'timeout' => 8,
    ],
    'order_search' => [
      'timeout' => 9,
    ],
    'cybersource_token_get' => [
      'timeout' => 10,
    ],
    'cybersource_token_process' => [
      'timeout' => 11,
    ],
    'customer_create' => [
      'timeout' => 8,
    ],
    'customer_search' => [
      'timeout' => 4,
    ],
    'checkoutcom_config_get' => [
      'timeout' => 4,
    ],
    'checkoutcom_token_get' => [
      'timeout' => 4,
    ],
    'checkoutcom_token_list' => [
      'timeout' => 4,
    ],
    'cnc_check' => [
      'timeout' => 6,
    ],
    'customer_me_get' => [
      'timeout' => 10,
    ],
    'default' => [
      'timeout' => 30,
    ],
  ],
  'drupal' => [
    'default' => [
      'timeout' => 30,
    ],
    'dm_search' => [
      'timeout' => 5,
    ],
    'customer_update' => [
      'timeout' => 10,
    ],
    'customer_authenticate' => [
      'timeout' => 15,
    ],
    'customer_password_set' => [
      'timeout' => 10,
    ],
    'store_search' => [
      'timeout' => 10,
    ],
    'order_get' => [
      'timeout' => 10,
    ],
    'stock_get' => [
      'timeout' => 5,
    ],
  ],
];
