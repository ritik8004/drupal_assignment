<?php

/**
 * @file
 * Utility file to expose functions to get data related to product.
 */

use Drupal\node\Entity\Node;
use Drupal\acq_sku\Entity\SKU;
use Drupal\file\Entity\File;

/**
 * Utility function to return media files for a product.
 *
 * @param int $productId
 *   Product node id.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_product_media($productId) {
  // Load product from id.
  $product = Node::load($productId);

  // Get SKU from product.
  $skuEntity = $product->get('field_skus')->first()->get('entity')->getValue();

  return alshaya_acm_product_get_sku_media($skuEntity);
}

/**
 * Utility function to return media files for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_sku_media($sku) {
  $media = [];

  $skuEntity = is_object($sku) ? $sku : SKU::loadFromSKU($sku);

  // For now we load only the image field.
  $imageField = $skuEntity->get('attr_image')->getValue();

  // We will return the array of File objects.
  foreach ($imageField as $image) {
    $media[] = File::load($image['target_id']);
  }

  return $media;
}
