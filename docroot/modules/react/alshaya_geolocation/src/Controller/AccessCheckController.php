<?php

namespace Drupal\alshaya_geolocation\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;

/**
 * Checks access for displaying geolocation pages.
 */
class AccessCheckController extends ControllerBase {

  /**
   * Checks access for a specific request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function accessCheck() {
    // Allow access to the routes only if the store finder status is set.
    return AccessResult::allowedIf($this->config('alshaya_geolocation.settings')->get('geolocation_enabled'))
      ->addCacheableDependency($this->config('alshaya_geolocation.settings'));
  }

}
