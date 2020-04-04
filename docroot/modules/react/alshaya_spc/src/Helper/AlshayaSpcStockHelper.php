<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\acq_sku\Entity\SKU;

/**
 * Class AlshayaSpcStockHelper.
 *
 * @package Drupal\alshaya_spc\Helper
 */
class AlshayaSpcStockHelper {

  /**
   * Refresh stock cache and Drupal cache of products in cart.
   *
   * @param mixed $cart
   *   Cart data.
   *
   * @return array
   *   Response data.
   */
  public function refreshStockForProductsInCart($cart = NULL) {
    $processed_parents = [];

    // If empty, simply return.
    if (empty($cart)) {
      return [];
    }

    $response = [];

    foreach ($cart['items'] ?? [] as $item) {
      if ($sku_entity = SKU::loadFromSku($item['sku'])) {
        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku_entity->getPluginInstance();
        $parent = $plugin->getParentSku($sku_entity);

        // Refresh Current Sku stock.
        $sku_entity->refreshStock();
        $plugin = $sku_entity->getPluginInstance();
        $response['stock'][$sku_entity->getSku()] = $plugin->isProductInStock($sku_entity);
        // Refresh parent stock once if exists for cart items.
        if ($parent instanceof SKU && !in_array($parent->getSku(), $processed_parents)) {
          $processed_parents[] = $parent->getSku();
          $parent->refreshStock();
          $plugin = $parent->getPluginInstance();
          $parent_in_stock = $plugin->isProductInStock($parent);
          if ($response['stock'][$sku_entity->getSku()]
            && !$parent_in_stock) {
            $response['stock'][$sku_entity->getSku()] = FALSE;
          }
        }
      }
    }

    return $response;
  }

}
