<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

/**
 * Provides a resource to get product details.
 *
 * @RestResource(
 *   id = "product_exclude_linked_v2",
 *   label = @Translation("Product Excluded Linked V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/product-exclude-linked/{sku}"
 *   }
 * )
 */
class ProductExcludeLinkedResourceV2 extends ProductExcludeLinkedResource {

  /**
   * Responds to GET requests.
   *
   * Returns sku data excluding linked skus of the given sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing sku data
   *   excluding linked skus of the given sku.
   */
  public function get(string $sku) {
    $sku = base64_decode($sku);
    return parent::get($sku);
  }

}
