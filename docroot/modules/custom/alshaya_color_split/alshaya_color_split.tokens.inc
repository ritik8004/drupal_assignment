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
function alshaya_color_split_token_info() {
  $info = [];

  $info['tokens']['node']['sku-color'] = [
    'name' => 'sku-color',
    'description' => t('Token to fetch color label for the SKU connected with the Product node.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_color_split_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  $color_split_settings = \Drupal::config('alshaya_color_split.settings');
  $attribute_code_color_label = $color_split_settings->get('attribute_code_color_label');
  if (isset($data['entity_type']) && ($data['entity_type'] == 'node') && (isset($tokens['sku-color']))) {
    /** @var \Drupal\alshaya_acm_product\SkuManager $sku_manager */
    $sku_manager = \Drupal::service('alshaya_acm_product.skumanager');

    /** @var \Drupal\alshaya_color_split\AlshayaColorSplitManager $manager */
    $manager = \Drupal::service('alshaya_color_split.manager');

    $color_label = '';

    // Get SKU for the node url alias is being generated for.
    if ($config_sku = $sku_manager->getSkuForNode($data['entity'])) {
      // Hard coded EN here as we always want English value for URL.
      $sku = SKU::loadFromSku($config_sku, 'en');

      // Apply this token only in case the SKU has style code attribute.
      if ($sku instanceof SKU && $sku_manager->fetchStyleCode($sku)) {
        if ($sku->bundle() === 'configurable') {
          $first_child = $sku_manager->getChildSkus($sku, TRUE);
          if (($first_child instanceof SKU) && ($first_child->hasField($attribute_code_color_label))) {

            // We always want English value in URL.
            if ($first_child->language()->getId() != 'en' && $first_child->hasTranslation('en')) {
              $first_child = $first_child->getTranslation('en');
            }

            $color_label = $first_child->get($attribute_code_color_label)->getString();
          }
        }
        elseif ($sku->bundle() === 'simple'
          && ($grouping_attribute = $manager->getGroupingAttribute($sku))
          && $sku->hasField('attr_' . $grouping_attribute)) {
          $color_label = $sku->get('attr_' . $grouping_attribute)->getString();
        }
      }
    }

    $replacements[$tokens['sku-color']] = $color_label;
  }

  return $replacements;
}
