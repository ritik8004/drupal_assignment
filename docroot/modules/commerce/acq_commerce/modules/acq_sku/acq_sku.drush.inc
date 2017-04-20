<?php

/**
 * @file
 * Provides drush commands for the products of Acquia Commerce connector.
 */

/**
 * Implements hook_drush_command().
 */
function acq_sku_drush_command() {
  $commands = [];

  $commands['sync-commerce-products'] = [
    'description' => 'Run a full synchronization of all commerce product records.',
    'aliases'     => ['acsp'],
    'examples'    => [
      'drush acsp' => 'Run a full product synchronization of all available products.',
    ],
  ];

  $commands['sync-commerce-cats'] = [
    'description' => 'Run a full synchronization of all commerce product category records.',
    'aliases'     => ['acsc'],
    'examples'    => [
      'drush acsc' => 'Run a full category synchronization of all available categories.',
    ],
  ];

  return ($commands);
}

/**
 * Implements drush product sync command.
 */
function drush_acq_sku_sync_commerce_products() {
  drush_print(dt('Synchronizing all commerce products, this usually takes some time...'));
  $container = \Drupal::getContainer();
  $container->get('acq_commerce.ingest_api')->productFullSync();
}

/**
 * Implements drush category sync command.
 */
function drush_acq_sku_sync_commerce_cats() {
  drush_print(dt('Synchronizing all commerce categories, please wait...'));
  $container = \Drupal::getContainer();
  $container->get('acq_sku.category_manager')->synchronizeTree('acq_product_category');
  drush_print(dt('Done.'));
}
