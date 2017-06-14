<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_acm_drush_command() {
  $commands = [];

  $commands['alshaya-acm-offline-categories-sync'] = [
    'description' => 'Imports commerce product categories from the codebase.',
    'aliases'     => ['aaocs'],
  ];

  $commands['alshaya-acm-offline-products-sync'] = [
    'description' => 'Imports commerce products from the codebase.',
    'aliases'     => ['aaops'],
    'options'     => [
      'limit' => 'Number of products to sync.',
      'skus' => 'SKUs to import (like query).',
    ],
  ];

  return $commands;
}

/**
 * Implements drush categories sync command.
 */
function drush_alshaya_acm_offline_categories_sync() {
  drush_print(dt('Synchronizing all commerce categories, please wait...'));
  $container = \Drupal::getContainer();

  $languages = \Drupal::languageManager()->getLanguages();
  foreach ($languages as $langcode => $language) {
    drush_print(dt('Dealing with @langcode ...', ['@langcode' => $langcode]));
    module_load_include('data', 'alshaya_acm', 'includes/categories_' . $langcode);
    global $_alshaya_acm_categories;

    $container->get('acq_sku.category_manager')->synchronizeTreeOffline('acq_product_category', $_alshaya_acm_categories);
  }
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

  global $_alshaya_acm_products;
  $query = drush_get_option('skus', '');
  $limit = (int) drush_get_option('limit', 0);

  $languages = \Drupal::languageManager()->getLanguages();
  foreach ($languages as $langcode => $language) {
    drush_print(dt('Dealing with @langcode ...', ['@langcode' => $langcode]));

    module_load_include('data', 'alshaya_acm', 'includes/products_' . $langcode);

    // Check if we need to import only specific SKUs.
    if ($query) {
      $_alshaya_acm_products = array_filter($_alshaya_acm_products, function ($sku) use ($query) {
        return strpos($sku['sku'], $query) !== FALSE;
      });
    }

    if ($limit) {
      $_alshaya_acm_products = array_slice($_alshaya_acm_products, 0, $limit);
    }

    $product_chunks = array_chunk($_alshaya_acm_products, 250, TRUE);

    // Save memory by unsetting global var.
    $_alshaya_acm_products = [];

    foreach ($product_chunks as $products) {
      // Reset static caches to release memory.
      drupal_static_reset();

      // Entity storage can blow up with caches so clear them out.
      $container = \Drupal::getContainer();
      /** @var \Drupal\Core\Entity\EntityManagerInterface $manager */
      $manager = $container->get('entity.manager');
      foreach ($manager->getDefinitions() as $id => $definition) {
        $manager->getStorage($id)->resetCache();
      }

      // Process chunk.
      $resource->post($products);
    }
  }

  drush_print(dt('Done.'));
}
