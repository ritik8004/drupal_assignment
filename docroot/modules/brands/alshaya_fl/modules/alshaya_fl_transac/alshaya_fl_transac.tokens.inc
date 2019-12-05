<?php

/**
 * @file
 * Token related functions for the alshaya_seo_transac.
 */

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\NodeInterface;

/**
 * Implements hook_token_info().
 */
function alshaya_fl_transac_token_info() {
  $info = [];

  $info['tokens']['alshaya_fl_transac']['product_color_text'] = [
    'name' => t('Product Color'),
    'description' => t('Text: with %color color'),
  ];

  $info['tokens']['alshaya_fl_transac']['product_material_text'] = [
    'name' => t('Category Name'),
    'description' => t('Leaf level category used in breadcrumb.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_fl_transac_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type !== 'alshaya_fl_transac' || empty($data['node']) || !($data['node'] instanceof NodeInterface)) {
    return $replacements;
  }

  /** @var \Drupal\node\NodeInterface $node */
  $node = $data['node'];

  /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
  $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
  $sku = SKU::loadFromSku($skuManager->getSkuForNode($node), $node->language()->getId());
  if (!($sku instanceof SKU)) {
    return $replacements;
  }

  foreach ($tokens as $key => $token) {
    if ($key === 'product_material_text') {
      $material = $sku->get('attr_material')->getString();
      $replacements[$token] = !empty($material) ? t('made by @material', ['@material' => $material]) : '';
    }
    elseif ($key === 'product_color_text') {
      $color = '';

      foreach (Configurable::getChildSkus($sku) ?? [] as $child_sku) {
        $child = SKU::loadFromSku($child_sku, $sku->language()->getId());

        if ($child instanceof SKU) {
          $color = $child->get('attr_color')->getString();
        }

        if (!empty($color)) {
          break;
        }
      }

      $replacements[$token] = $color ? t('with @color color', ['@color' => $color]) : '';
    }
  }

  return $replacements;
}
