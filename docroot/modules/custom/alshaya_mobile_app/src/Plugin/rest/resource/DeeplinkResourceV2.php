<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;

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
   * Prefix used for the v2 endpoint.
   */
  public const V2_ENDPOINT_PREFIX = '/rest/v2/';

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the deeplink.
   */
  public function get() {
    $alias = $this->requestStack->query->get('url');
    $url = $this->mobileAppUtility->getDeeplinkForResource($alias);
    // Check if sku is encoded.
    if (str_contains($url, 'product-exclude-linked')) {
      $url_array = explode('product-exclude-linked', $url);
      $sku = $url_array[1];
      $encoded_sku = base64_encode(str_replace('/', '', $sku));
      $url = self::V2_ENDPOINT_PREFIX . 'product-exclude-linked/' . $encoded_sku;
    }
    return new ModifiedResourceResponse(['deeplink' => $url]);
  }

}
