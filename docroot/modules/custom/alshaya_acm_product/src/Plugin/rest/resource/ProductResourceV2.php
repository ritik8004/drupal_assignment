<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

/**
 * Provides a resource to get product details.
 *
 * @RestResource(
 *   id = "product_v2",
 *   label = @Translation("Product V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/product/{sku}"
 *   }
 * )
 */
class ProductResourceV2 extends ProductResource {

  /**
   * Responds to GET requests.
   *
   * Returns available delivery method data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing delivery methods data.
   */
  public function get(string $sku) {
    $sku = base64_decode($sku);
    return parent::get($sku);
  }

}
