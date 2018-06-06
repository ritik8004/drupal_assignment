<?php

/**
 * @file
 * Provides drush commands for the products of Acquia Commerce connector.
 */

use Drupal\Core\Cache\Cache;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\rest\resource\ProductSyncResource;

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
      'drush acsp en 50' => 'Run a full product synchronization of all available products in store linked to en and page size 50.',
      'drush acsp en 50 --skus=\'M-H3495 130 2  FW\',\'M-H3496 130 004FW\',\'M-H3496 130 005FW\'' => 'Synchronize sku data for the skus M-H3495 130 2  FW, M-H3496 130 004FW & M-H3496 130 005FW only in store linked to en and page size 50.',
      'drush acsp en 50 --category_id=1234' => 'Synchronize sku data for the skus in category with id 1234 only in store linked to en and page size 50.',
    ],
    'arguments'     => [
      'langcode' => 'Language code for which we want to import products.',
      'page_size' => 'Page/Chunk size for importing products in chunks.',
    ],
    'required-arguments' => TRUE,
    'options'     => [
      'skus' => 'SKUs to import (like query).',
      'category_id' => 'Magento category id to sync the products for.'
    ],
  ];

  $commands['sync-commerce-cats'] = [
    'description' => 'Run a full synchronization of all commerce product category records.',
    'aliases'     => ['acsc'],
    'examples'    => [
      'drush acsc' => 'Run a full category synchronization of all available categories.',
    ],
  ];

  $commands['commerce-cats-remove-duplicates'] = [
    'description' => 'Remove all duplicate categories available in system.',
    'aliases'     => ['acccrd'],
    'examples'    => [
      'drush acccrd' => 'Remove all duplicate categories available in system.',
    ],
  ];

  $commands['commerce-products-remove-duplicates'] = [
    'description' => 'Remove all duplicate products available in system.',
    'aliases'     => ['accprd'],
    'examples'    => [
      'drush accprd' => 'Remove all duplicate products available in system.',
    ],
  ];

  $commands['clean-synced-data'] = [
    'description' => 'Flush all commerce data from the site (Products, SKUs, Product Categories and Product Options).',
    'aliases'     => ['accd'],
    'examples'    => [
      'drush acsc' => 'Flush all commerce data from the site (Products, SKUs, Product Categories and Product Options).',
    ],
  ];

  $commands['sync-stock'] = [
    'description' => 'Sync stock into all SKU entities using API.',
  ];

  $commands['get-stock-cache'] = [
    'description' => 'Get stock cache for particualar SKU',
    'aliases'     => ['acgsc'],
    'arguments'     => [
      'sku' => 'SKU to get stock of.',
    ],
    'examples'    => [
      'drush acgsc SKU' => 'Get stock cache for particualar SKU',
    ],
  ];

  $commands['clean-stock-cache'] = [
    'description' => 'Flush the stock cache.',
    'aliases'     => ['accsc'],
    'options'     => [
      'sku' => 'SKU to clean stock of.',
    ],
    'examples'    => [
      'drush accsc' => 'Flush stock cache for all SKUs.',
      'drush accsc --sku=SKU' => 'Flush stock cache for particular SKU',
    ],
  ];

  $commands['clear-linked-skus-cache'] = [
    'description' => 'Clear linked SKUs cache.',
    'aliases'     => ['acclsc'],
    'options'     => [
      'sku' => 'SKU to clean linked skus cache of.',
    ],
    'examples'    => [
      'drush accsc' => 'Clear linked SKUs cache for all SKUs.',
      'drush accsc --sku=SKU' => 'Clear linked SKUs cache for particular SKU.',
    ],
  ];


  $commands['sync-commerce-products-test'] = [
    'description' => 'Run a partial synchronization of commerce product records synchronously for testing / dev.',
    'aliases'     => ['acdsp'],
    'arguments'   => [
      'count' => 'Number of product records to sync.',
    ],
    'examples'    => [
      'drush acdsp' => 'Run a partial synchronization of commerce product records synchronously for testing / dev.',
    ],
  ];

  return ($commands);
}

/**
 * Implements drush product sync command.
 */
function drush_acq_sku_sync_commerce_products($langcode, $page_size) {
  $langcode = strtolower($langcode);

  $store_id = \Drupal::service('acq_commerce.i18n_helper')->getStoreIdFromLangcode($langcode);

  if (empty($store_id)) {
    drush_print(dt("Store id not found for provided language code."));
    return;
  }

  $page_size = (int) $page_size;

  if ($page_size <= 0) {
    drush_print(dt("Page size must be a positive integer."));
    return;
  }

  $skus = drush_get_option('skus');

  $category_id = drush_get_option('category_id');

  // Apply only one filer at a time.
  if ($category_id) {
    $skus = '';
  }

  // Ask for confirmation from user if attempt is to run full sync.
  if (empty($skus) && empty($category_id)) {
    $confirm = dt('Are you sure you want to import all products for @language language?', [
      '@language' => $langcode,
    ]);

    if (!drush_confirm($confirm)) {
      return drush_user_abort();
    }
  }

  drush_print(dt('Requesting all commerce products for selected language code...'));
  $container = \Drupal::getContainer();
  $container->get('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, $skus, $category_id, $page_size);
  drush_print(dt('Done.'));
}

/**
 * Implements drush product sync test / dev command.
 */
function drush_acq_sku_sync_commerce_products_test($count = 100) {
  drush_print(dt('Synchronizing @count commerce products for testing / dev...', ['@count' => $count]));
  $container = \Drupal::getContainer();
  $products = \Drupal::service('acq_commerce.api')->getProducts($count);
  $product_sync_resource = ProductSyncResource::create($container, [], NULL, NULL);
  $product_sync_resource->post($products);
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
  \Drupal::logger('acq_sku')->notice('Synchronizing all commerce product options, please wait...');
  $container = \Drupal::getContainer();
  $container->get('acq_sku.product_options_manager')->synchronizeProductOptions();
  \Drupal::logger('acq_sku')->notice('Product attribute sync completed.');
}

/**
 * Implements drush commerce-cats-remove-duplicates command.
 */
function drush_acq_sku_commerce_cats_remove_duplicates() {
  drush_print(dt('Cleaning all commerce categories, please wait...'));

  $db = \Drupal::database();

  /** @var \Drupal\taxonomy\TermStorageInterface $termStorage */
  $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

  /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
  $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

  $query = $db->select('taxonomy_term__field_commerce_id', 'ttfci');
  $query->addField('ttfci', 'field_commerce_id_value', 'commerce_id');
  $query->groupBy('commerce_id');
  $query->having('count(*) > 1');
  $result = $query->execute()->fetchAllKeyed(0, 0);

  if (empty($result)) {
    drush_print(dt('No duplicate categories found.'));
    return;
  }

  foreach ($result as $commerce_id) {
    drush_print(dt('Duplicate categories found for commerce id: @commerce_id.', [
      '@commerce_id' => $commerce_id,
    ]));

    $query = $db->select('taxonomy_term__field_commerce_id', 'ttfci');
    $query->addField('ttfci', 'entity_id', 'tid');
    $query->condition('ttfci.field_commerce_id_value', $commerce_id);
    $query->orderBy('tid', 'DESC');
    $tids = $query->execute()->fetchAllKeyed(0, 0);

    foreach ($tids as $tid) {
      $query = $nodeStorage->getQuery();
      $query->condition('field_category', $tid);
      $nodes = $query->execute();

      if (empty($nodes)) {
        drush_print(dt('No nodes found for tid: @tid for commerce id: @commerce_id. Deleting', [
          '@commerce_id' => $commerce_id,
          '@tid' => $tid,
        ]));

        $term = $termStorage->load($tid);
        $term->delete();

        unset($tids[$tid]);

        // Break the loop if only one left now, we might not have any products
        // added yet and categories are synced which means there will be no
        // nodes for any term.
        if (count($tids) == 1) {
          break;
        }
      }
      else {
        drush_print(dt('@count nodes found for tid: @tid for commerce id: @commerce_id. Not Deleting', [
          '@commerce_id' => $commerce_id,
          '@tid' => $tid,
          '@count' => count($nodes),
        ]));
      }
    }
  }

  drush_print(dt('Done.'));
}

/**
 * Implements drush clean synced data command.
 */
function drush_acq_sku_clean_synced_data() {
  if (!drush_confirm(dt("Are you sure you want to clean commerce data?"))) {
    return drush_user_abort();
  }
  drush_print(dt('Cleaning synced commerce data, please wait...'));

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
    $query->condition('type', 'acq_product');
    $product_entities = $query->execute();
    foreach ($product_entities as $entity_id) {
      $context['sandbox']['results'][] = [
        'type' => 'node',
        'entity_id' => $entity_id,
      ];
    }

    // Get all acq_sku entities.
    $query = \Drupal::entityQuery('acq_sku');
    $sku_entities = $query->execute();
    foreach ($sku_entities as $entity_id) {
      $context['sandbox']['results'][] = [
        'type' => 'acq_sku',
        'entity_id' => $entity_id,
      ];
    }

    // Get all taxonomy_term entities.
    $categories = ['acq_product_category', 'sku_product_option'];
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $categories, 'IN');
    $cat_entities = $query->execute();
    foreach ($cat_entities as $entity_id) {
      $context['sandbox']['results'][] = [
        'type' => 'taxonomy_term',
        'entity_id' => $entity_id,
      ];
    }

    // Allow other modules to add data to be deleted when cleaning up.
    \Drupal::moduleHandler()->alter('acq_sku_clean_synced_data', $context);

    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_id'] = 0;
    $context['sandbox']['max'] = count($context['sandbox']['results']);
  }

  $results = [];
  if (isset($context['sandbox']['results']) && !empty($context['sandbox']['results'])) {
    $results = $context['sandbox']['results'];
  }

  $results = array_slice($results, isset($context['sandbox']['current']) ? $context['sandbox']['current'] : 0, DELETE_BATCH_COUNT);

  $delete = [];

  foreach ($results as $key => $result) {
    $context['results'][] = $results['type'] . ' : ' . $result['entity_id'];
    $context['sandbox']['progress']++;
    $context['sandbox']['current_id'] = $result['entity_id'];

    $delete[$result['type']][] = $result['entity_id'];

    // Update our progress information.
    $context['sandbox']['current']++;
  }

  foreach ($delete as $type => $entity_ids) {
    try {
      $storage = \Drupal::entityTypeManager()->getStorage($type);
      $entities = $storage->loadMultiple($entity_ids);
      $storage->delete($entities);
    }
    catch (\Exception $e) {
      \Drupal::logger('acq_sku')->error($e->getMessage());
    }
  }

  $context['message'] = 'Processed ' . $context['sandbox']['progress'] . ' out of ' . $context['sandbox']['max'] . '.';

  if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
 * Implements drush sync-stock command.
 */
function drush_acq_sku_sync_stock() {
  $db = \Drupal::database();

  $query = $db->select('acq_sku_field_data', 'asfd');
  $query->addField('asfd', 'sku', 'sku');
  $query->condition('asfd.type', 'simple');
  $query->isNull('asfd.stock');
  $result = $query->execute()->fetchAllKeyed(0, 0);

  drush_print(sprintf('Found %d skus without stock info.', count($result)));

  if (empty($result)) {
    return;
  }

  drush_print('Processing in batches of 25');

  $batches = array_chunk($result, 25);

  // Entity storage can blow up with caches so clear them out.
  $container = \Drupal::getContainer();

  /** @var \Drupal\Core\Entity\EntityManagerInterface $manager */
  $manager = $container->get('entity.manager');

  // We always process for en.
  $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();

  $counter = 0;

  foreach ($batches as $batch) {
    foreach ($batch as $sku) {
      $sku_entity = SKU::loadFromSku($sku, $langcode, FALSE, FALSE);

      // Sanity check, another process can delete the sku.
      if (empty($sku_entity)) {
        continue;
      }

      // Check again this sku doesn't have stock saved once.
      // Another process might have updated the info while we were processing.
      $stock = $sku_entity->get('stock')->getString();
      if (!($stock === '' || $stock === NULL)) {
        continue;
      }

      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface $plugin */
      $plugin = $sku_entity->getPluginInstance();
      $plugin->getProcessedStock($sku_entity, TRUE);

      // Reset static caches to release memory.
      drupal_static_reset();

      // Adding sleep of 200 ms to ensure API calls don't overload the server.
      usleep(200);
    }

    $counter++;

    drush_print(sprintf('Processed batch %d of %d.', $counter, count($batches)));

    foreach ($manager->getDefinitions() as $id => $definition) {
      $manager->getStorage($id)->resetCache();
    }
  }

  drush_print('Done');
}

/**
 * Implements drush get stock cache command.
 */
function drush_acq_sku_get_stock_cache($sku) {
  if ($sku_entity = SKU::loadFromSku($sku)) {
    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface $plugin */
    $plugin = $sku_entity->getPluginInstance();
    drush_print($plugin->getProcessedStock($sku_entity));
  }
  else {
    drush_print(dt('SKU not found.'));
  }
}

/**
 * Implements drush clean stock cache command.
 */
function drush_acq_sku_clean_stock_cache() {
  // Check if we are asked to clear cache of specific SKU.
  if ($sku = drush_get_option('sku', '')) {
    if ($sku_entity = SKU::loadFromSku($sku)) {
      $sku_entity->clearStockCache();

      drush_print(dt('Invalidated stock cache for @sku.', [
        '@sku' => $sku,
      ]));
    }

    return;
  }

  if (!drush_confirm(dt('Are you sure you want to clean stock cache?'))) {
    return drush_user_abort();
  }

  \Drupal::service('cache.stock')->deleteAllPermanent();

  drush_print(dt('Deleted all cache for stock.'));
}

/**
 * Implements drush commerce-products-remove-duplicates command.
 */
function drush_acq_sku_commerce_products_remove_duplicates() {
  drush_print(dt('Removing duplicates in commerce products, please wait...'));

  $skus_to_sync = [];

  $db = \Drupal::database();

  /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
  $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

  $query = $db->select('acq_sku_field_data', 't1');
  $query->addField('t1', 'id', 'id');
  $query->addField('t1', 'sku', 'sku');
  $query->leftJoin('acq_sku_field_data', 't2', 't1.sku = t2.sku');
  $query->where('t1.id != t2.id');
  $result = $query->execute()->fetchAllKeyed(0, 1);

  if (empty($result)) {
    drush_print(dt('No duplicate skus found.'));
  }
  else {
    $skus = [];

    foreach ($result as $id => $sku) {
      $skus[$sku][$id] = $id;
      $skus_to_sync[$sku] = $sku;
    }

    foreach ($skus as $sku => $ids) {
      drush_print(dt('Duplicate skus found for sku: @sku with ids: @ids.', [
        '@sku' => $sku,
        '@ids' => implode(', ', $ids),
      ]));

      // Always delete the one with higher id, first one will have more
      // translations.
      sort($ids);

      // Remove the first id which we don't want to delete.
      array_shift($ids);

      foreach ($ids as $id) {
        drush_print(dt('Deleting sku with id @id for sku @sku.', [
          '@sku' => $sku,
          '@id' => $id,
        ]));

        $sku_entity = SKU::load($id);
        $sku_entity->delete();
      }
    }
  }

  $query = $db->select('node__field_skus', 't1');
  $query->addField('t1', 'entity_id', 'id');
  $query->addField('t1', 'field_skus_value', 'sku');
  $query->leftJoin('node__field_skus', 't2', 't1.field_skus_value = t2.field_skus_value');
  $query->where('t1.entity_id != t2.entity_id');
  $result = $query->execute()->fetchAllKeyed(0, 1);

  if (empty($result)) {
    drush_print(dt('No duplicate product nodes found.'));
  }
  else {
    $nids_to_delete = [];
    $skus = [];

    foreach ($result as $id => $sku) {
      $skus[$sku][$id] = $id;
      $skus_to_sync[$sku] = $sku;
    }

    foreach ($skus as $sku => $ids) {
      drush_print(dt('Duplicate nodes found for sku: @sku with ids: @ids.', [
        '@sku' => $sku,
        '@ids' => implode(', ', $ids),
      ]));

      // Always delete the one with higher nid, first one will have proper
      // url alias.
      sort($ids);

      // Remove the first id which we don't want to delete.
      array_shift($ids);

      foreach ($ids as $id) {
        drush_print(dt('Deleting node with id @id for sku @sku.', [
          '@sku' => $sku,
          '@id' => $id,
        ]));

        $nids_to_delete[$id] = $id;
      }
    }

    if ($nids_to_delete) {
      $nodeStorage->delete($nodeStorage->loadMultiple($nids_to_delete));
    }
  }

  if ($skus_to_sync) {
    $sku_texts = implode(',', $skus_to_sync);

    drush_print(dt('Requesting resync for skus @skus.', [
      '@skus' => $sku_texts,
    ]));

    foreach (\Drupal::service('acq_commerce.i18n_helper')->getStoreLanguageMapping() as $langcode => $store_id) {
      // Using very small page size to avoid any issues for skus which already
      // had corrupt data.
      \Drupal::service('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, $sku_texts, NULL, 5);
    }
  }

  drush_print(dt('Done.'));
}

/**
 * Implements drush clear-linked-skus-cache command.
 */
function drush_acq_sku_clear_linked_skus_cache() {
  // Check if we are asked to clear cache of specific SKU.
  if ($sku = drush_get_option('sku', '')) {
    if ($sku_entity = SKU::loadFromSku($sku)) {
      Cache::invalidateTags([
        'acq_sku:linked_skus:' . $sku_entity->id(),
        'acq_sku:' . $sku_entity->id()
      ]);

      drush_print(dt('Invalidated linked SKUs cache for @sku.', [
        '@sku' => $sku,
      ]));
    }

    return;
  }

  if (!drush_confirm(dt('Are you sure you want to clear linked SKUs cache for all SKUs?'))) {
    return drush_user_abort();
  }

  \Drupal::service('cache.linked_skus')->deleteAll();

  drush_print(dt('Cleared all linked SKUs cache.'));
}
