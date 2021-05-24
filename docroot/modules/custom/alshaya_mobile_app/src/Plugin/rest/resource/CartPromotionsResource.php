<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource to init k-net request and get url.
 *
 * @RestResource(
 *   id = "cart_promotions",
 *   label = @Translation("Get all promotions for cart."),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/promotion/cart/{cart_id}"
 *   }
 * )
 */
class CartPromotionsResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * Get all promotions for cart.
   *
   * @param string $cart_id
   *   Cart ID.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Cacheable response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function get(string $cart_id) {
    // @todo Remove the resource in future release.
    // We disable the config during install in alshaya_mobile_app.
    return [];
  }

}
