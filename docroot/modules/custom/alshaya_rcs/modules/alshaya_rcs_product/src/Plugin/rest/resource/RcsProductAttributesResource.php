<?php

namespace Drupal\alshaya_rcs_product\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource to get list of all product attributes.
 *
 * @RestResource(
 *   id = "rcsproductoptions",
 *   label = @Translation("Returns list all rcs product attributes options."),
 *   uri_paths = {
 *     "canonical" = "/rcs/product-attribute-options"
 *   }
 * )
 */
class RcsProductAttributesResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $response_data = \Drupal::service('alshaya_rcs_product.product_attributes_helper')->getProductAttributesOptions();
    $response = new ResourceResponse($response_data);

    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags([
      'taxonomy_term_list:sku_product_option',
    ]);
    $response->addCacheableDependency($cacheableMetadata);
    return $response;
  }

}
