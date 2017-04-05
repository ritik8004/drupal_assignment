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

/**
 * Utility function to return media files for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return array
 *   Array of configurable field values.
 */
function alshaya_acm_product_get_sku_configurable_values($sku) {
  $configurableFieldValues = [];

  $skuEntity = is_object($sku) ? $sku : SKU::loadFromSKU($sku);

  $fields = \Drupal::config('alshaya_acm_product.sku_base_fields')->get('fields');
  $configurableFields = array_filter($fields, function ($field) {
    return (bool) $field['configurable'];
  });

  foreach ($configurableFields as $key => $field) {
    $fieldKey = 'attr_' . $key;
    $configurableFieldValues[$fieldKey] = [
      // @TODO: Find a way to avoid translating variable below.
      // @codingStandardsIgnoreLine
      'label' => t($field['label']),
      'value' => $skuEntity->get($fieldKey)->getString(),
    ];
  }

  return $configurableFieldValues;
}

/**
 * Utility function to get parent SKU for a configurable child sku.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return object
 *   Loaded SKU entity.
 */
function alshaya_acm_product_get_parent_sku_by_sku($sku) {
  $sku = is_object($sku) ? $sku->getSKU() : $sku;

  // Execute the query and get all SKU which have current SKU in
  // field_configured_skus.
  $query = \Drupal::entityQuery('acq_sku');
  $query->condition('field_configured_skus', $sku);
  $ids = $query->execute();

  if (count($ids) != 1) {
    \Drupal::logger('alshaya_acm_product')->error(
      'SKU @sku has no or multiple parent SKUs.',
      ['@sku' => $sku]
    );

    throw new RuntimeException('No or multiple parent SKUs found');
  }

  // Get the first id.
  $parentSkuId = array_shift($ids);

  // Return fully load SKU.
  return SKU::load($parentSkuId);
}
