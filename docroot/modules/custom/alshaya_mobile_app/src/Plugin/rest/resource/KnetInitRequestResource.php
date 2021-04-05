<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource to init k-net request and get url.
 *
 * @RestResource(
 *   id = "knet_init_request",
 *   label = @Translation("K-Net init request and get URL"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/knet/init/{cart_id}"
 *   }
 * )
 */
class KnetInitRequestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * Initialise k-net request and return state_key and url.
   *
   * @param string $cart_id
   *   Cart ID.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Non-cacheable response object.
   */
  public function get(string $cart_id) {
    // @todo Remove the resource in future release.
    // We disable the config during install in alshaya_transac.
    return [];
  }

}
