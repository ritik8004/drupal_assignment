<?php

/**
 * @file
 * Alshaya vs transac tokens file.
 */

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens_alter().
 */
function alshaya_vs_transac_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
  if ($context['type'] == 'node' && !empty($context['data']['node']) && isset($context['tokens']['title'])) {
    $node = $context['data']['node'];
    $route_match = \Drupal::routeMatch();
    // We have the condition on Route,
    // to ensure it doesn't get applied for cases like URL generation.
    if ($route_match->getRouteName() == 'entity.node.canonical' && $node->bundle() === 'acq_product') {
      /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
      $sku = SKU::loadFromSku(\Drupal::service('alshaya_acm_product.skumanager')->getSkuForNode($node), $node->language()->getId());
      if ($sku instanceof SKU) {
        // This is done for all Meta title, Page title.
        $title = _alshaya_vs_transac_get_title($sku, 'meta');
        $replacements[$context['tokens']['title']] = $title;
      }
    }
  }
}
