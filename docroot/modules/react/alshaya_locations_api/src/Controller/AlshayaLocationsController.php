<?php

namespace Drupal\alshaya_locations_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Alshaya Locations Controller.
 */
class AlshayaLocationsController extends ControllerBase {

  /**
   * Stores controller for site.
   *
   * @return object
   *   Click and collect for site.
   */
  public function stores($data = NULL) {
    // Mock file read for now.
    $file = file_get_contents("https://hmkw.alshaya.lndo.site/modules/react/alshaya_geolocation/file/mockdata.json");
    return new JsonResponse(json_decode($file));
  }

}
