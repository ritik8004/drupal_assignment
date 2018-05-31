<?php

/**
 * @file
 * Provides drush commands related to Product position.
 */

use Drupal\taxonomy\Entity\Term;

/**
 * Implements hook_drush_command().
 */
function acq_sku_position_drush_command() {
  $commands = [];

  $commands['position-sync'] = [
    'description' => 'Drush command to sync sku product position based on category.',
    'aliases' => ['aapps'],
    'arguments'   => [
      'position_type' => 'Name of the position type.',
    ],
    'examples' => [
      'drush aapps' => 'Sync product position based on category, by default "position".',
      'drush aapps myargument' => 'Sync product position based on category, by "myargument".',
    ],
  ];

  return $commands;
}

/**
 * Sync product position based on category.
 */
function drush_acq_sku_position_position_sync($position_type = 'position') {
  \Drupal::logger('acq_sku_position_position')->notice('Product position sync in progress...');

  $database = \Drupal::database();

  // Get all product category terms.
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('acq_product_category');
  foreach ($terms as $term) {
    // Find the commerce id from the term. Skip if not found.
    $commerce_id = Term::load($term->tid)->get('field_commerce_id')->value;
    if (!$commerce_id) {
      continue;
    }

    // Get product position for this category from commerce backend.
    try {
      $response = \Drupal::service('acq_commerce.api')->getProductPosition($commerce_id);
      if (!is_array($response)) {
        continue;
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('acq_sku_position_position')->notice('Exception while fetching position for category @name (tid: @tid). The category probably does not exist in commerce backend', [
        '@name' => $term->name,
        '@tid' => $term->tid,
      ]);
      continue;
    }

    // Skip sync if error is found in the response for a particular category.
    if (is_array($response) && isset($response['message'])) {
      \Drupal::logger('acq_sku_position_position')->notice('Error in position sync for @name (tid: @tid). Response: @message', [
        '@name' => $term->name,
        '@tid' => $term->tid,
        '@message' => $response['message'],
      ]);
      continue;
    }

    // Start product position sync for this category.
    \Drupal::logger('acq_sku_position_position')->notice('Product position sync for @name (tid: @tid) in progress...', [
      '@name' => $term->name,
      '@tid' => $term->tid,
    ]);

    // Delete existing records of position for this category.
    $database->delete('acq_sku_position')
      ->condition('tid', $term->tid)
      ->condition('position_type', $position_type)
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

    $is_data_available = FALSE;
    $query = $database->insert('acq_sku_position')
      ->fields(['nid', 'tid', 'position', 'position_type']);
    foreach ($response as $product_position) {
      if (isset($nids[$product_position['sku']])) {
        // Insert new position data for the product.
        $record = [
          'nid' => $nids[$product_position['sku']],
          'tid' => $term->tid,
          'position' => $product_position['position'],
          'position_type' => $position_type,
        ];
        $is_data_available = TRUE;
        $query->values($record);
      }
    }

    if ($is_data_available) {
      $query->execute();
    }
  }

  // Allow other modules to take action after position sync finished.
  \Drupal::moduleHandler()->invokeAll('acq_sku_position_sync_finished');

  \Drupal::logger('acq_sku_position_position')->notice('Product position sync completed!');
}
