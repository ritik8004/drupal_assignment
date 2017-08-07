<?php

/**
 * @file
 * Hooks specific to the acq_sku module.
 */

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
function hook_acq_sku_product_node_alter(Drupal\node\NodeInterface $node, array $product) {

}

/**
 * @} End of "addtogroup hooks".
 */
