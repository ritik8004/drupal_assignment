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
 * Alter (add/update/delete) fields to be added to SKU entity.
 *
 * @param array $fields
 *   Fields array.
 */
function hook_acq_sku_base_field_additions_alter(array &$fields = []) {

}

/**
 * Allow modules to do something after base fields are updated.
 *
 * For instance, create facets, create facet blocks,
 *
 * @param array $fields
 *   Fields array.
 * @param string $op
 *   Operation performed on fields.
 */
function acq_sku_base_fields_updated(array $fields, $op = 'add') {

}

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
 * @param mixed $parent
 *   Parent Taxonomy term if available.
 */
function hook_acq_sku_commerce_category_alter(TermInterface $term, array $category, $parent) {

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
 * Alter old categories data that needs to be deleted after category sync.
 *
 * @param array $result
 *   Result array to alter.
 */
function hook_acq_sku_sync_categories_delete_alter(array &$result) {

}

/**
 * Alter the options added to cart item.
 *
 * @param array $options
 *   Options to be added to cart item.
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   Parent sku which is being added to cart.
 */
function hook_acq_sku_configurable_cart_options_alter(array &$options, SKU $sku) {

}

/**
 * @} End of "addtogroup hooks".
 */
