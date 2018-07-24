<?php

/**
 * @file
 * Provides drush commands for alshaya_product_options.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_product_options_drush_command() {
  $commands = [];

  $commands['alshaya-sync-commerce-product-options'] = [
    'description' => 'Sync options for all product attributes.',
    'aliases'     => ['aacspo', 'sync-options'],
    'examples'    => [
      'drush sync-options' => 'Sync options for all product attributes.',
    ],
  ];

  $commands['alshaya-sync-commerce-product-option'] = [
    'description' => 'Sync options for particular product attribute.',
    'aliases'     => ['sync-option'],
    'arguments'     => [
      'attribute_code' => 'Attribute code for which we want to sync options.',
    ],
    'required-arguments' => TRUE,
    'examples'    => [
      'drush sync-option actual_color_label_code' => 'Sync options for particular product attribute.',
    ],
  ];

  return ($commands);
}

/**
 * Callback for alshaya-sync-commerce-product-options drush command.
 */
function drush_alshaya_product_options_alshaya_sync_commerce_product_options() {
  drush_print('Alshaya - Synchronizing all commerce product options, please wait...');

  /** @var \Drupal\alshaya_product_options\ProductOptionsHelper $service */
  $service = \Drupal::service('alshaya_product_options.helper');
  $service->synchronizeProductOptions();

  drush_print('Sync completed.');
}

/**
 * Callback for alshaya-sync-commerce-product-option drush command.
 *
 * @param string $attribute_code
 *   Attribute code to sync options for.
 */
function drush_alshaya_product_options_alshaya_sync_commerce_product_option($attribute_code) {
  drush_print(dt('Alshaya - Synchronizing commerce product options for @attribute_code, please wait...', [
    '@attribute_code' => $attribute_code,
  ]));

  $languages = \Drupal::languageManager()->getLanguages();

  /** @var \Drupal\alshaya_product_options\ProductOptionsHelper $service */
  $service = \Drupal::service('alshaya_product_options.helper');

  foreach ($languages as $langcode => $language) {
    $service->syncProductOption($attribute_code, $langcode);
  }

  drush_print('Sync completed.');
}
