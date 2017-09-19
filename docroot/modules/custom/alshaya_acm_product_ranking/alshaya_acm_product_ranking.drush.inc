<?php

/**
 * @file
 * Provides drush commands related to Product ranking.
 */

use Drupal\taxonomy\Entity\Term;

/**
 * Implements hook_drush_command().
 */
function alshaya_acm_product_ranking_drush_command() {
  $commands = [];

  $commands['ranking-sync'] = [
    'description' => 'Drush command to sync product ranking based on category.',
    'aliases' => ['aaprs'],
    'arguments'   => [
      'ranking_type' => 'Name of the ranking type.',
    ],
  ];

  return $commands;
}

/**
 * Sync product rankings based on category.
 */
function drush_alshaya_acm_product_ranking_ranking_sync($ranking_type) {
  drush_print(dt('Product ranking sync in progress...'));
  // Get root category configuration.
  $filter_root_category = \Drupal::config('acq_commerce.conductor')->get('filter_root_category');

  $database = \Drupal::database();

  // Get all product category terms.
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('acq_product_category');
  foreach ($terms as $term) {
    // Skip root category if configured in settings.
    if ($filter_root_category && isset($term->parents[0]) && $term->parents[0] == '0') {
      continue;
    }

    // Find the magento commerce id from the term. Skip if not found.
    $commerce_id = Term::load($term->tid)->get('field_commerce_id')->value;
    if (!$commerce_id) {
      continue;
    }

    // Get product ranking for this category from Magento.
    $response = \Drupal::service('alshaya_api.api')->invokeApi("categories/$commerce_id/products", [], 'GET');
    $response = json_decode($response, TRUE);

    // Skip sync if error is found in the response for a particular category.
    if (is_array($response) && isset($response['message'])) {
      drush_print(dt("Error in ranking sync for $term->name (tid: $term->tid). Response: " . $response['message']));
      continue;
    }

    // Start product ranking sync for this category.
    drush_print(dt("Product ranking sync for $term->name (tid: $term->tid) in progress..."));

    // Delete existing records of rankings for this category.
    $database->delete('alshaya_acm_product_ranking')
      ->condition('tid', $term->tid)
      ->condition('ranking_type', $ranking_type)
      ->execute();

    // Get all skus from the response.
    $skus = array_column($response, 'sku');
    if (empty($skus)) {
      continue;
    }

    // Get all product nids from skus.
    $query = $database->select('node__field_skus', 'n');
    $query->fields('n', ['field_skus_value', 'entity_id']);
    $query->condition('n.bundle', 'acq_product');
    $query->condition('n.field_skus_value', $skus, 'IN');
    $nids = $query->execute()->fetchAllKeyed();
    // Skip if not product found for any sku.
    if (empty($nids)) {
      continue;
    }

    foreach ($response as $product_rank) {
      if (isset($nids[$product_rank['sku']])) {
        // Insert new ranking data for the product.
        $fields = [
          'nid' => $nids[$product_rank['sku']],
          'tid' => $term->tid,
          'rank' => $product_rank['position'],
          'ranking_type' => $ranking_type,
        ];
        $database->insert('alshaya_acm_product_ranking')
          ->fields($fields)
          ->execute();
      }
    }
  }

  drush_print(dt('Product ranking sync completed!'));
}
