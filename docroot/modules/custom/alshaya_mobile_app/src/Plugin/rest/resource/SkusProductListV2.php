<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

/**
 * Provides a resource to get attributes for SKU's list.
 *
 * @RestResource(
 *   id = "skus_product_list_v2",
 *   label = @Translation("SKUs Product List V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/skus/product-list",
 *      "https://www.drupal.org/link-relations/create" = "/rest/v2/skus/product-list"
 *   }
 * )
 */
class SkusProductListV2 extends SkusProductList {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing attributes of skus.
   */
  public function get(string $sku_list = '') {
    $sku_list = $this->requestStack->query->get('skus');
    $sku = base64_decode($sku_list);
    return parent::get($sku);
  }

}
