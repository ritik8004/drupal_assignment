<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;

/**
 * Checks access for displaying store finder pages..
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
    return AccessResult::allowedIf($this->config('alshaya_stores_finder.settings')->get('stores_finder_page_status'));
  }

}
