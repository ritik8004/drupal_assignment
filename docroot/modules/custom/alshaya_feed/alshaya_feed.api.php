<?php

/**
 * @file
 * Hooks specific to the alshaya_feed module.
 */

use Drupal\acq_commerce\SKUInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter product variant info to match brand needs.
 *
 * @param array $variant
 *   Variant array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 */
function hook_alshaya_feed_variant_info_alter(array &$variant, SKUInterface $sku) {

}
