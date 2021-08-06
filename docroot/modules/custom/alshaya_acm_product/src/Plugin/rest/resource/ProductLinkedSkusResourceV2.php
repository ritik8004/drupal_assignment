<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

/**
 * Provides a resource to get product details with linked skus.
 *
 * @RestResource(
 *   id = "product_linked_skus_v2",
 *   label = @Translation("Product Linked Skus V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/product/{sku}/linked"
 *   }
 * )
 */
class ProductLinkedSkusResourceV2 extends ProductLinkedSkusResource {

  /**
   * Responds to GET requests.
   *
   * Returns linked skus of the given sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing linked skus of the given sku.
   */
  public function get(string $sku) {
    $sku = base64_decode($sku);
    return parent::get($sku);
  }

}
