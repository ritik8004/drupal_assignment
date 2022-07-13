<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

/**
 * Provides a resource to get details for SKU's list.
 *
 * @RestResource(
 *   id = "skus_product_list_v2",
 *   label = @Translation("SKUs Product List V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/skus/product-list",
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
  public function get() {
    $sku_list = $this->requestStack->query->get('skus');
    $sku = base64_decode($sku_list);
    return parent::getSkuListData($sku);
  }

}
