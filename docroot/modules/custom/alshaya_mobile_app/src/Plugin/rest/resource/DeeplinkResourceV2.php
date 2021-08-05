<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

/**
 * Provides a resource to get deeplink.
 *
 * @RestResource(
 *   id = "deeplink_v2",
 *   label = @Translation("Deeplink V2"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/deeplink"
 *   }
 * )
 */
class DeeplinkResourceV2 extends DeeplinkResource {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the deeplink.
   */
  public function get() {
    $alias = $this->requestStack->query->get('url');
    return parent::getDeeplink($alias);
  }

}
