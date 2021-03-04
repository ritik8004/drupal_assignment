<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

/**
 * Provides a resource to get stock details.
 *
 * @RestResource(
 *   id = "stock_v2",
 *   label = @Translation("Stock V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/stock/{sku}"
 *   }
 * )
 */
class StockResourceV2 extends StockResource {

  /**
   * Responds to GET requests.
   *
   * Returns stock info for sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing stock data.
   */
  public function get(string $sku) {
    $sku = base64_decode($sku);
    return parent::get($sku);
  }

}
