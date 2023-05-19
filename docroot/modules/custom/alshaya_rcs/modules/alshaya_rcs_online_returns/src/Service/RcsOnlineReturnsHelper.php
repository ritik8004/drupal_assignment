<?php

namespace Drupal\alshaya_rcs_online_returns\Service;

use Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper;

/**
 * Overrides Online Returns Helper.
 *
 * @package Drupal\alshaya_rcs_online_returns\Service
 */
class RcsOnlineReturnsHelper extends OnlineReturnsHelper {

  /**
   * Wrapper function to prepare product data.
   *
   * @param array $products
   *   Products array.
   *
   * @return array
   *   Processed product data.
   */
  public function prepareProductsData(array $products) {
    foreach ($products as $key => $item) {
      // Update the order total based on the item qty.
      if ($products[$key]['qty_refunded'] > 0
        && $products[$key]['qty_refunded'] <= $products[$key]['qty_ordered']) {
        $products[$key]['qty_ordered'] -= $products[$key]['qty_refunded'];
        // Update the `ordered` flag.
        $products[$key]['ordered'] = $products[$key]['qty_ordered'];

        // Updating total value as `qty_ordered` is updated.
        $products[$key]['total'] = alshaya_acm_price_format(
          $products[$key]['qty_ordered'] * $products[$key]['price_incl_tax'],
        );
      }
      // Check if it is big ticket item or not.
      $products[$key]['is_big_ticket'] = $this->isBigTicketItem($item);
    }
    return $products;
  }

}
