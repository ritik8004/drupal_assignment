<?php

namespace Drupal\alshaya_add_to_bag\Services;

use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\node\NodeInterface;

/**
 * Helper class for Search indexing.
 */
class AddToBagIndexHelper {

  /**
   * Add To bag attribute name for index on Algolia.
   */
  const ADD_TO_BAG_ATTRIBUTE = 'atb_product_data';

  /**
   * SKU Info Helper service.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * AddToBagIndexHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   SKU Info Helper service.
   */
  public function __construct(
    SkuInfoHelper $sku_info_helper
  ) {
    $this->skuInfoHelper = $sku_info_helper;
  }

  /**
   * Get the items for search indexing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   The SKU entity.
   * @param string $langcode
   *   The langcode in which data needs to be fetched.
   *
   * @return array
   *   The data to be indexed. Returns empty array if the feature is disabled.
   */
  public function getItemsToIndex(NodeInterface $node, SKUInterface $sku, string $langcode) {
    $data = [];

    // Set SKU type.
    $data['sku_type'] = $sku->bundle();

    // Set the buyable status for the product. The config holding the
    // buyable status, i.e. alshaya_acm_product.settings attr_is_buyable will
    // be sent via Drupal Settings to front-end.
    $data['is_buyable'] = (bool) $sku->get('attr_is_buyable')->getString();

    if ($data['sku_type'] === 'simple') {
      // Set cart title.
      $data['cart_title'] = $this->skuInfoHelper->getCartTitle($sku);

      // Set cart image.
      $data['cart_image'] = $this->skuInfoHelper->getCartImage($sku);

      // Index max sale quantity. Whether max sale quantity is enabled/disabled
      // will be passed in drupalSettings.
      $stock_info = $this->skuInfoHelper->stockInfo($sku);
      $data['max_sale_qty'] = $stock_info['max_sale_qty'];
    }

    return $data;
  }

}
