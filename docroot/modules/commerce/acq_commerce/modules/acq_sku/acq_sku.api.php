<?php

/**
 * @file
 * Hooks specific to the acq_sku module.
 */

use Drupal\acq_sku\Entity\SKU;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter product node before it is saved during insert or update.
 *
 * Product data from API is passed here to allow other modules to read from
 * the data provided by API and update product node accordingly.
 *
 * @param \Drupal\node\NodeInterface $node
 *   Node to alter.
 * @param array $product
 *   Array containing details provided by API.
 */
function hook_acq_sku_product_node_alter(NodeInterface $node, array $product) {

}

/**
 * Alter SKU entity before it is saved during insert or update.
 *
 * Product data from API is passed here to allow other modules to read from
 * the data provided by API and update SKU entity accordingly.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU to alter.
 * @param array $product
 *   Array containing details provided by API.
 */
function hook_acq_sku_product_sku_alter(SKU $sku, array $product) {

}

/**
 * Alter Taxonomy Term before it is saved during insert or update.
 *
 * Category data from API is passed here to allow other modules to read from
 * the data provided by API and update Taxonomy Term accordingly.
 *
 * @param \Drupal\taxonomy\TermInterface $term
 *   Taxonomy term to alter.
 * @param array $category
 *   Array containing details provided by API.
 * @param \Drupal\taxonomy\TermInterface $parent
 *   Parent Taxonomy term to if available.
 */
function hook_acq_sku_commerce_category_alter(TermInterface $term, array $category, TermInterface $parent = NULL) {

}

/**
 * Alter (add) data that needs to be deleted while removing all synced data.
 *
 * @param mixed $context
 *   Whole batch context array.
 */
function hook_acq_sku_clean_synced_data_alter(&$context) {

}

/**
 * @} End of "addtogroup hooks".
 */
