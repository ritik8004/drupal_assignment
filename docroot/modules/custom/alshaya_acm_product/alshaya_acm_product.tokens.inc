<?php

/**
 * @file
 * Contains token info & their callbacks.
 */

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function alshaya_acm_product_token_info() {
  $info = [];
  $info['tokens']['node']['sku-color'][] = 'Token to fetch color label for the SKU connected with the Product node.';
  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_acm_product_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if (isset($data['entity_type']) && ($data['entity_type'] == 'node') && (isset($tokens['sku-color']))) {
    /** @var \Drupal\alshaya_acm_product\SkuManager $sku_manager */
    $sku_manager = \Drupal::service('alshaya_acm_product.skumanager');
    $color_label = '';
    if ($config_sku = $sku_manager->getSkuForNode($data['entity'])) {
      $sku = SKU::loadFromSku($config_sku);
      $color_label = $sku->get('attr_color_label')->getString();
    }
    $replacements[$tokens['sku-color']] = $color_label;
  }

  return $replacements;
}
