<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['alshaya_backend_calls_options'] = [
  'drupal' => [
    'default' => [
      'timeout' => 30,
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
      'timeout' => 15,
    ],
    'customer_search' => [
      'timeout' => 5,
    ],
    'order_search' => [
      'timeout' => 10,
    ],
    'customer_create' => [
      'timeout' => 20,
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
    'checkoutcom_config' => [
      'timeout' => 30,
    ],
    'get_saved_card' => [
      'timeout' => 30,
    ],
    'delete_saved_card' => [
      'timeout' => 30,
    ],
    'postpay_config' => [
      'timeout' => 10,
    ],
    'tabby_config' => [
      'timeout' => 10,
    ],
    'checkoutcom_token_list' => [
      'timeout' => 30,
    ],
    'checkoutcom_token_delete' => [
      'timeout' => 30,
    ],
    'aura_dictionary_config' => [
      'timeout' => 30,
    ],
    'online_returns_config' => [
      'timeout' => 10,
    ],
    'subscribe_newsletter' => [
      'timeout' => 30,
    ],
  ],
];
