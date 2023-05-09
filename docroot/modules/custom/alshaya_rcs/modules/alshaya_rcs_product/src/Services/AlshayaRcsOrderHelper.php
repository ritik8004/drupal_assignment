<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_spc\Helper\AlshayaSpcOrderHelper;

/**
 * Class Alshaya Rcs Order Helper.
 *
 * This overrides the methods of AlshayaSpcOrderHelper for RCS products.
 */
class AlshayaRcsOrderHelper extends AlshayaSpcOrderHelper {

  /**
   * {@inheritDoc}
   */
  public function getSkuDetails(array $item) {
    $parent_sku = $item['product_type'] === 'configurable'
      ? $item['extension_attributes']['parent_product_sku']
      : NULL;

    $data = [
      'sku' => $item['sku'],
      'parentSKU' => $parent_sku,
      'product_type' => $item['product_type'],
      'freeItem' => ($item['price_incl_tax'] == 0) || ($item['price_incl_tax'] == SkuManager::FREE_GIFT_PRICE),
      'title' => $item['name'],
      'finalPrice' => $this->skuInfoHelper->formatPriceDisplay((float) $item['price']),
      'id' => $item['item_id'],
      // Added quantity of product for checkout olapic pixel.
      'qtyOrdered' => $item['qty_ordered'],
    ];
    $this->setEgiftDetailsToOrderItem($item, $data);

    return $data;
  }

}
