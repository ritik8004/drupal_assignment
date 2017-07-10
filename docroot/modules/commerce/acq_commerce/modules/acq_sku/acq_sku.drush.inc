<?php

/**
 * @file
 * Provides drush commands for the products of Acquia Commerce connector.
 */

const DELETE_BATCH_COUNT = 200;

/**
 * Implements hook_drush_command().
 */
function acq_sku_drush_command() {
  $commands = [];

  $commands['sync-commerce-product-options'] = [
    'description' => 'Run a full synchronization of all commerce product options.',
    'aliases'     => ['acspo'],
    'examples'    => [
      'drush acsp' => 'Run a full product synchronization of all available product options.',
    ],
  ];

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

  $commands['clean-synced-data'] = [
    'description' => 'Flush all commerce data from the site (Products, SKUs and Product Categories).',
    'aliases'     => ['accd'],
    'examples'    => [
      'drush acsc' => 'Flush all commerce data from the site (Products, SKUs and Product Categories).',
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


/**
 * Implements drush product attribute sync command.
 */
function drush_acq_sku_sync_commerce_product_options() {
  drush_print(dt('Synchronizing all commerce product options, please wait...'));
  $container = \Drupal::getContainer();
  $container->get('acq_sku.product_options_manager')->synchronizeProductOptions();
  \Drupal::logger('acq_sku')->notice('Product attribute sync completed.');
  drush_print(dt('Done.'));
}

/**
 * Implements drush clean synced data command.
 */
function drush_acq_sku_clean_synced_data() {
  if (!drush_confirm(dt("Are you sure you want to clean commerce data (Products, SKUs, Product Categories and Product Options)?"))) {
    return drush_user_abort();
  }
  drush_print(dt('Cleaning synced commerce data (Products, SKUs and Product Categories), please wait...'));

  // Set batch operation.
  $batch = [
    'title' => t('Clean synced data'),
    'init_message' => t('Cleaning synced commerce data starting...'),
    'operations' => [
      ['_acq_sku_clean_process', []],
    ],
    'progress_message' => t('Processed @current out of @total.'),
    'error_message' => t('Synced data could not be cleaned because an error occurred.'),
    'finished' => '_acq_sku_clean_finished',
  ];

  batch_set($batch);
  drush_backend_batch_process();
  drush_print(dt('Synced commerce data cleaned.'));
}

/**
 * Function to process entity delete operation.
 *
 * @param mixed|array $context
 *   The batch current context.
 */
function _acq_sku_clean_process(&$context) {
  // Use the $context['sandbox'] at your convenience to store the
  // information needed to track progression between successive calls.
  if (empty($context['sandbox'])) {
    // Get all the entities that need to be deleted.
    $context['sandbox']['results'] = [];

    // Get all acq_product entities.
    $query = \Drupal::entityQuery('node');
    $query->addTag('node');
    $query->condition('type', 'acq_product');
    $product_entities = $query->execute();
    foreach ($product_entities as $product_entity) {
      $context['sandbox']['results'][] = [$product_entity, 'node'];
    }

    // Get all acq_sku entities.
    $query = \Drupal::entityQuery('acq_sku');
    $sku_entities = $query->execute();
    foreach ($sku_entities as $sku_entity) {
      $context['sandbox']['results'][] = [$sku_entity, 'acq_sku'];
    }

    // Get all acq_product_category entities.
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', 'acq_product_category');
    $cat_entities = $query->execute();
    foreach ($cat_entities as $cat_entity) {
      $context['sandbox']['results'][] = [$cat_entity, 'taxonomy_term'];
    }

    // Get all sku_product_option entities.
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', 'sku_product_option');
    $sku_product_option_entities = $query->execute();
    foreach ($sku_product_option_entities as $sku_product_option_entity) {
      $context['sandbox']['results'][] = [$sku_product_option_entity, 'taxonomy_term'];
    }

    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_id'] = 0;
    $context['sandbox']['max'] = count($context['sandbox']['results']);
  }

  $results = [];
  if (isset($context['sandbox']['results']) && !empty($context['sandbox']['results'])) {
    $results = $context['sandbox']['results'];
  }

  $results = array_slice($results, isset($context['sandbox']['current']) ? $context['sandbox']['current'] : 0, DELETE_BATCH_COUNT);

  foreach ($results as $key => $result) {
    $context['results'][] = $results[0] . ' : ' . $result[1];
    $context['sandbox']['progress']++;
    $context['sandbox']['current_id'] = $result[0];

    // Load the entity.
    $entity = \Drupal::entityTypeManager()->getStorage($result[1])->load($result[0]);
    if ($entity) {
      try {
        // Delete entity.
        $entity->delete();
      }
      catch (\Exception $e) {
        \Drupal::logger('acq_sku')->error($e->getMessage());
      }
    }
    // Update our progress information.
    $context['sandbox']['current']++;
  }
  $context['message'] = 'Processed ' . $context['sandbox']['progress'] . ' out of ' . $context['sandbox']['max'] . '.';
  if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}
