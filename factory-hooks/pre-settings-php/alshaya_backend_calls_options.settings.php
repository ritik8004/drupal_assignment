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
    'default' => [
      'timeout' => 30,
    ],
    'order_place' => [
      'timeout' => 60,
    ],
    'cart_update' => [
      'timeout' => 15,
    ],
    'cart_get' => [
      'timeout' => 10,
    ],
    'cart_create' => [
      'timeout' => 5,
    ],
    'cart_associate' => [
      'timeout' => 15,
    ],
    'cart_estimate_shipping' => [
      'timeout' => 15,
    ],
    'cart_payment_methods' => [
      'timeout' => 10,
    ],
    'cart_selected_payment' => [
      'timeout' => 10,
    ],
    'cart_search' => [
      'timeout' => 10,
    ],
    'order_search' => [
      'timeout' => 5,
    ],
    'cybersource_token_get' => [
      'timeout' => 15,
    ],
    'cybersource_token_process' => [
      'timeout' => 15,
    ],
    'customer_create' => [
      'timeout' => 10,
    ],
    'customer_search' => [
      'timeout' => 10,
    ],
    'checkoutcom_config_get' => [
      'timeout' => 10,
    ],
    'checkoutcom_token_list' => [
      'timeout' => 5,
    ],
    'cnc_check' => [
      'timeout' => 10,
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
    'product_get' => [
      'timeout' => 5,
    ],
    'customer_search' => [
      'timeout' => 5,
    ],
    'order_search' => [
      'timeout' => 10,
    ],
    'customer_create' => [
      'timeout' => 10,
    ],
    'cnc_check' => [
      'timeout' => 10,
    ],
    'cart_selected_payment' => [
      'timeout' => 10,
    ],
    'cart_get' => [
      'timeout' => 10,
    ],
    'get_categories' => [
      'timeout' => 300,
    ],
  ],
  'appointment_booking' => [
    'default' => [
      'timeout' => 30,
    ],
    'customer_me_get' => [
      'timeout' => 10,
    ],
  ],
];
