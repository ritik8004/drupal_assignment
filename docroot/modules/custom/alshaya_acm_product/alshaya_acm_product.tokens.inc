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

    // Get SKU for the node url alias is being generated for.
    if ($config_sku = $sku_manager->getSkuForNode($data['entity'])) {
      $sku = SKU::loadFromSku($config_sku);

      // Apply this token only in case the SKU has style code attribute.
      if ($sku_manager->fetchStyleCode($sku)) {
        $first_child = $sku_manager->getChildSkus($sku, TRUE);
        if ($first_child instanceof SKU) {
          $color_label = $first_child->get('attr_color_label')->getString();
        }
      }
    }
    $replacements[$tokens['sku-color']] = $color_label;
  }

  return $replacements;
}
