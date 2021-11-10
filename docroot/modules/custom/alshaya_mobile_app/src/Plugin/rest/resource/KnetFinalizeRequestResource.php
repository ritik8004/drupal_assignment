<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource to get final status and data of transaction.
 *
 * @RestResource(
 *   id = "knet_finalize_request",
 *   label = @Translation("K-Net get final status and data of transaction"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/knet/finalize/{state_key}"
 *   }
 * )
 */
class KnetFinalizeRequestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * K-Net get final status and data of transaction.
   *
   * @param string $state_key
   *   State Key.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Non-cacheable response object.
   */
  public function get(string $state_key) {
    // @todo Remove the resource in future release.
    // We disable the config during install in alshaya_transac.
    return [];
  }

}
