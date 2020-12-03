<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Product Order Limit.
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
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(
    SkuManager $sku_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->skuManager = $sku_manager;
    $this->configFactory = $config_factory;
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
    $order_limit_msg = '';
    $hide_max_qty_limit_message = $this->configFactory->get('alshaya_acm.settings')->get('hide_max_qty_limit_message');

    if ($limit_exceeded) {
      $build = [
        '#theme' => 'product_order_quantity_limit',
        '#message' => $this->t('Purchase limit has been reached'),
        '#limit_reached' => TRUE,
      ];
      $order_limit_msg = render($build);
    }
    elseif (!empty($max_sale_qty) && !$hide_max_qty_limit_message) {
      $build = [
        '#theme' => 'product_order_quantity_limit',
        '#message' => $this->t('Limited to @max_sale_qty per customer', ['@max_sale_qty' => $max_sale_qty]),
        '#limit_reached' => FALSE,
      ];
      $order_limit_msg = render($build);
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
   *   Quantity currently in cart.
   */
  public function getCartItemQtyLimit($variant_sku) {
    $qty_limit = 0;
    $variant = $variant_sku instanceof SKU ? $variant_sku : SKU::loadFromSku($variant_sku);
    // Get cart items by sku.
    $cart_items = alshaya_acm_get_cart_items_by_sku();
    $cart_items = !empty($cart_items) ? array_column($cart_items, 'qty', 'sku') : [];
    // Variant itself is parent if it's NULL.
    $variant_parent = $this->skuManager->getParentSkuBySku($variant_sku);

    if (!empty($cart_items)) {
      if ($variant->bundle() === 'simple' && $variant_parent === NULL) {
        $qty_limit = in_array($variant_sku, array_keys($cart_items)) ? $cart_items[$variant_sku] : 0;
      }
      else {
        // If variant bundle is not simple and parent is NULL
        // then variant itself is parent.
        $variant_parent = $variant_parent == NULL ? $variant : $variant_parent;
        $variant_parent = $variant_parent instanceof SKU ? $variant_parent : SKU::loadFromSku($variant_parent);
        // Check if limit set at parent level.
        $variant_parent_sku = $variant_parent->getSku();
        $plugin = $variant_parent->getPluginInstance();
        $parent_max_sale_qty = $plugin->getMaxSaleQty($variant_parent_sku);
        // Limit is set at parent level.
        if (!empty($parent_max_sale_qty)) {
          foreach ($cart_items as $item => $qty) {
            $cart_item_parent = $this->skuManager->getParentSkuBySku($item);
            $cart_item_parent_sku = $cart_item_parent ? $cart_item_parent->getSku() : NULL;

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

}
