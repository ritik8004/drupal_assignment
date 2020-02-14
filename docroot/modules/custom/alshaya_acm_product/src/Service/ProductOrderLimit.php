<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\acq_commerce\SKUInterface;

/**
 * Class ProductOrderLimit.
 *
 * @package Drupal\alshaya_acm_product
 */
class ProductOrderLimit {

  use StringTranslationTrait;

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   */
  public function __construct(
    SkuManager $sku_manager
  ) {
    $this->skuManager = $sku_manager;
  }

  /**
   * Wrapper function get max sale qty message.
   *
   * @param string $max_sale_qty
   *   Max sale qty.
   * @param bool $limit_exceeded
   *   Limit exceeded.
   *
   * @return string
   *   Order limit message.
   */
  public function maxSaleQtyMessage($max_sale_qty, $limit_exceeded = FALSE) {
    if ($limit_exceeded) {
      $order_limit_msg = '<span class="order-qty-limit-msg-inner-wrapper limit-reached">' . $this->t('Purchase limit has been reached') . '</span>';
    }
    else {
      $order_limit_msg = (!empty($max_sale_qty)) ? '<span class="order-qty-limit-msg-inner-wrapper">' . $this->t('Limited to @max_sale_qty per customer', ['@max_sale_qty' => $max_sale_qty]) . '</span>' : '';
    }

    return $order_limit_msg;
  }

  /**
   * Wrapper function get qty of current variant in cart.
   *
   * @param string $variant_sku
   *   Variant Sku.
   *
   * @return array
   *   Quantity limit.
   */
  public function getCartItemQtyLimit($variant_sku) {
    $qty_limit = 0;
    $variant_parent = $this->skuManager->getParentSkuBySku($variant_sku);
    // Variant itself is parent if it's NULL here.
    $variant_parent = $variant_parent === NULL ? $variant_sku : $variant_parent;
    // Get cart items by sku.
    $cart_items = alshaya_acm_get_cart_items_by_sku();
    $cart_items = !empty($cart_items) ? array_column($cart_items, 'qty', 'sku') : [];

    if (!empty($cart_items)) {
      if ($variant_parent) {
        // Check if limit set at parent level.
        $plugin = $variant_parent->getPluginInstance();
        $variant_parent_sku = $variant_parent->getSku();
        $parent_max_sale_qty = $plugin->getMaxSaleQty($variant_parent_sku);
        if (!empty($parent_max_sale_qty)) {
          foreach ($cart_items as $item => $qty) {
            $cart_item_parent_sku = $this->skuManager->getParentSkuBySku($item)->getSku();
            if ($cart_item_parent_sku === $variant_parent_sku) {
              $qty_limit += $qty;
            }
          }
        }
        else {
          $qty_limit = in_array($variant_sku, array_keys($cart_items)) ? $cart_items[$variant_sku] : 0;
        }
      }
    }
    return $qty_limit;
  }

  /**
   * Wrapper function to get max sale qty variables.
   *
   * @param string $sku
   *   Sku.
   * @param string $max_sale_qty
   *   Max sale qty.
   *
   * @return array
   *   Max sale qty variables.
   */
  public function getMaxSaleQtyVariables($sku, $max_sale_qty) {
    if (!empty($max_sale_qty)) {
      // Check product qty in cart.
      $cart_qty = $this->getCartItemQtyLimit($sku);

      if ($cart_qty && ($cart_qty >= $max_sale_qty)) {
        $order_limit_msg = $this->maxSaleQtyMessage($max_sale_qty, TRUE);
        $limit_exceeded = TRUE;
      }
      else {
        $order_limit_msg = $this->maxSaleQtyMessage($max_sale_qty);
        $limit_exceeded = FALSE;
      }
    }
    $max_sale_qty_variables = [
      'maxSaleQty' => (int) $max_sale_qty,
      'orderLimitMsg' => isset($order_limit_msg) ? $order_limit_msg : '',
      'orderLimitExceeded' => isset($limit_exceeded) ? $limit_exceeded : FALSE,
    ];

    return $max_sale_qty_variables;
  }

  /**
   * Helper function to get parent max sale qty if set.
   *
   * @param string $sku
   *   Sku.
   *
   * @return array
   *   Parent max sale qty.
   */
  public function getParentMaxSaleQty($sku) {
    $parent_sku = $this->skuManager->getParentSkuBySku($sku);

    if ($parent_sku instanceof SKUInterface) {
      $plugin = $parent_sku->getPluginInstance();
      $max_sale_qty = $plugin->getMaxSaleQty($parent_sku);
    }
    else {
      $plugin = $sku->getPluginInstance();
      $max_sale_qty = $plugin->getMaxSaleQty($sku);
    }

    return isset($max_sale_qty) ? $max_sale_qty : NULL;
  }

}
