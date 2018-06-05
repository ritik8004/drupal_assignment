<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Site\Settings;

/**
 * Implements hook_drush_command().
 */
function alshaya_acm_drush_command() {
  $commands = [];

  $commands['alshaya-acm-offline-products-sync'] = [
    'description' => 'Imports commerce products from the local files.',
    'aliases'     => ['aaops'],
    'options'     => [
      'limit' => 'Number of products to sync.',
      'skus' => 'SKUs to import (like query).',
    ],
  ];

  $commands['alshaya-acm-print-category-menu-tree'] = [
    'description' => 'Print the category menu true for specified language.',
    'aliases'     => ['aapcmt'],
    'arguments'     => [
      'langcode' => 'Language code for which we want to get category tree.',
    ],
    'required-arguments' => TRUE,
    'examples'    => [
      'drush aapcmt en' => 'Print the category menu true for en language.',
    ],
  ];

  $commands['alshaya-reset-config'] = [
    'description' => 'Reset all config from settings.',
  ];

  $commands['alshaya-switch-config'] = [
    'description' => 'Switch some config to connect Drupal to specific ACM and MDC.',
    'alias' => ['asc'],
    'options' => [
      'acm' => 'The ACM key to connect Drupal to (ex: mcsa_dev, hmkw_qa, hmsa_uat, ...).',
      'mdc' => 'The MDC key to connect Drupal to (ex: mc_dev, hm_qa, vs_uat, ...).',
    ],
    'examples' => [
      'drush asc --acm=mcsa_dev --mdc=mc_dev' => 'Connect Drupal to mcsa_dev ACM and mc_dev MDC.',
      'drush asc --acm=hmkw_qa' => 'Connect Drupal to hmkw_qa ACM.',
    ],
  ];

  return $commands;
}

/**
 * Implements drush switch config command.
 */
function drush_alshaya_switch_config() {
  $acm = drush_get_option('acm', '');
  $mdc = drush_get_option('mdc', '');

  if (empty($acm) && empty($mdc)) {
    drush_print(dt('Please provide an ACM or a MDC key to switch the config.'));
    return;
  }

  if (!empty($acm)) {
    include_once DRUPAL_ROOT . '/../factory-hooks/environments/conductor.php';
    $acms = alshaya_get_conductor_host_data();
  }

  if (!empty($mdc)) {
    include_once DRUPAL_ROOT . '/../factory-hooks/environments/magento.php';
    $mdcs = alshaya_get_magento_host_data();
  }
}

/**
 * Implements drush products sync command.
 */
function drush_alshaya_acm_offline_products_sync() {
  $brand_module = \Drupal::configFactory()->get('alshaya.installed_brand')->get('module');

  // Get country code.
  $country_code = Unicode::strtolower(Settings::get('country_code'));

  // Check if the site is configured for a specific brand.
  if (empty($brand_module)) {
    drush_print(dt('The site is not configured for a specific brand yet.'));
    return;
  }

  $path = drupal_get_path('module', $brand_module);

  // Check if the directory containing the data files exists.
  if (!file_exists($path . '/data/')) {
    drush_print(dt('The directory @directory does not exist. Please create and upload appropriate data files.', ['@directory' => $path . '/data/']));
    return;
  }

  $container = \Drupal::getContainer();

  // Get the REST endpoint information to POST products.
  $resource_storage = $container->get('entity_type.manager')->getStorage('rest_resource_config');
  $resource_config = $resource_storage->load('acq_productsync');
  $resource = $resource_config->getResourcePlugin();

  // Get the options from the drush command.
  $query = drush_get_option('skus', '');
  $limit = (int) drush_get_option('limit', 0);

  global $_alshaya_acm_products;
  foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
    // We check if the data file exists for this language.
    if (!file_exists($path . '/data/products_' . $country_code . '_' . $langcode . '.data')) {
      continue;
    }

    module_load_include('data', $brand_module, 'data/products_' . $country_code . '_' . $langcode);

    // Check if we need to import only specific SKUs.
    if ($query) {
      $_alshaya_acm_products = array_filter($_alshaya_acm_products, function ($sku) use ($query) {
        return strpos($sku['sku'], $query) !== FALSE;
      });
    }

    // Check if we need to import a limited set of products.
    if ($limit) {
      $_alshaya_acm_products = array_slice($_alshaya_acm_products, 0, $limit);
    }

    if (empty($_alshaya_acm_products)) {
      drush_print(dt('No @language products in @filename', ['@language' => strtolower($language->getName()), '@filename' => $path . '/data/products_' . $country_code . '_' . $langcode . '.data']));
      continue;
    }

    // Prepare chunks of products to avoid memory issue.
    $product_chunks = array_chunk($_alshaya_acm_products, 250, TRUE);

    // Save memory by unsetting global var.
    $_alshaya_acm_products = [];

    foreach ($product_chunks as $i => $products) {
      drush_print(dt('Synchronizing chunk @i of @total of @language products from @filename',
        [
          '@i' => $i + 1,
          '@total' => count($product_chunks),
          '@language' => strtolower($language->getName()),
          '@filename' => $path . '/data/products_' . $country_code . '_' . $langcode . '.data',
        ]
      ));

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

/**
 * Implements drush alshaya-acm-print-category-menu-tree.
 *
 * @param string $langcode
 *   Language code for which we want to get category tree.
 */
function drush_alshaya_acm_print_category_menu_tree($langcode) {
  /** @var \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree */
  $product_category_tree = \Drupal::service('alshaya_acm_product_category.product_category_tree');

  $tree = $product_category_tree->getCategoryTree($langcode);

  foreach ($tree as $level1) {
    drush_print($level1['label']);

    foreach ($level1['child'] as $level2) {
      drush_print('--' . $level2['label']);

      foreach ($level2['child'] as $level3) {
        drush_print('----' . $level3['label']);
      }
    }
  }
}

/**
 * Implements drush alshaya-reset-config.
 */
function drush_alshaya_acm_alshaya_reset_config() {
  // Force reset all the settings.
  \Drupal::service('alshaya_acm.config_check')->checkConfig(TRUE);

  // Reset country specific settings.
  \Drupal::service('alshaya_acm.config_check')->resetCountrySpecificSettings();
}
