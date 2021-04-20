<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource for getting product urls and images list for SKU.
 *
 * @RestResource(
 *   id = "sku_images",
 *   label = @Translation("SKU Images and Product URLs"),
 *   uri_paths = {
 *     "canonical" = "/skus/list",
 *     "https://www.drupal.org/link-relations/create" = "/skus/list"
 *   }
 * )
 */
class SKUImagesResource extends ResourceBase {

  /**
   * Responds to POST requests.
   *
   * Returns a url and images for requested SKUs/language.
   *
   * @param array $request
   *   Array containing SKUs and language code.
   */
  public function post(array $request) {
    // @todo Remove the rest API in future release.
    return [];
  }

}
