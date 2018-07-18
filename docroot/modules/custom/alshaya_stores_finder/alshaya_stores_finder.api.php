<?php

/**
 * @file
 * Hooks introduced by alshaya_stores_finder.
 */

use Drupal\node\NodeInterface;

/**
 * Alter store node before it is saved during insert or update.
 *
 * Stores data from API is passed here to allow other modules to read from
 * the data provided by API and update store node accordingly.
 *
 * @param \Drupal\node\NodeInterface $node
 *   Node to alter.
 * @param array $store
 *   Array containing details provided by API.
 */
function hook_alshaya_stores_finder_store_update_alter(NodeInterface $node, array $store) {

}

/**
 * Alter marker label position.
 *
 * @param array $label_position
 *   Array of label position override.
 */
function hook_alshaya_stores_finder_marker_label_position_alter(array &$label_position) {

}
