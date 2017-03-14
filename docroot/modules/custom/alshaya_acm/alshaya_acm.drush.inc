<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_acm_drush_command() {
  $commands = array();

  $commands['alshaya-acm-offline-categories-sync'] = [
    'description' => 'Imports commerce product categories from the codebase.',
    'aliases'     => ['aaocs'],
  ];

  $commands['alshaya-acm-offline-products-sync'] = [
    'description' => 'Imports commerce products from the codebase.',
    'aliases'     => ['aaops'],
  ];

  return($commands);
}

/**
 * Implements drush categories sync command.
 */
function drush_alshaya_acm_offline_categories_sync() {
  drush_print(dt('Synchronizing all commerce categories, please wait...'));
  $container = \Drupal::getContainer();

  module_load_include('data', 'alshaya_acm', 'includes/categories');
  global $_alshaya_acm_categories;

  $container->get('acq_sku.category_manager')->synchronizeTreeOffline('acq_product_category', $_alshaya_acm_categories);
  drush_print(dt('Done.'));
}

/**
 * Implements drush products sync command.
 */
function drush_alshaya_acm_offline_products_sync() {
  drush_print(dt('Synchronizing all commerce products, please wait...'));
  $container = \Drupal::getContainer();
  $resource_storage = $container->get('entity_type.manager')->getStorage('rest_resource_config');
  $resource_config = $resource_storage->load('acq_productsync');
  $resource = $resource_config->getResourcePlugin();

  module_load_include('data', 'alshaya_acm', 'includes/products');
  global $_alshaya_acm_products;

  $resource->post($_alshaya_acm_products);
  drush_print(dt('Done.'));
}
