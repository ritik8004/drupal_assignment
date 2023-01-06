<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a resource to get list of all categories.
 *
 *  @todo This is no longer used and should be deleted.
 *
 * @RestResource(
 *   id = "rcscategories",
 *   label = @Translation("List all rcs categories with enrichment data"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/categories"
 *   }
 * )
 */
class AlshayaRcsCategoryResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $response = new ResourceResponse([]);
    return $response;
  }

}
