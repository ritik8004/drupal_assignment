<?php

/**
 * @file
 * Hooks specific to the alshaya_pims module.
 */

/**
 * Allow other modules to alter media items array for products.
 *
 * @param array $media
 *   Media data to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 */
function hook_alshaya_pims_media_items_alter(array $media, SKUInterface $sku) {

}
