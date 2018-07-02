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
    'description' => 'Run a full synchronization of all commerce product options.',
    'aliases'     => ['aacspo', 'sync-options'],
    'examples'    => [
      'drush acsp' => 'Run a full product synchronization of all available product options.',
    ],
  ];

  return ($commands);
}

/**
 * Implements drush product attribute sync command.
 */
function drush_alshaya_product_options_alshaya_sync_commerce_product_options() {
  $logger = \Drupal::logger('alshaya_product_options');
  $logger->notice('Alshaya - Synchronizing all commerce product options, please wait...');

  /** @var \Drupal\alshaya_product_options\ProductOptionsHelper $service */
  $service = \Drupal::service('alshaya_product_options.helper');
  $service->synchronizeProductOptions();

  $logger->notice('Alshaya Product attribute sync completed.');
}
