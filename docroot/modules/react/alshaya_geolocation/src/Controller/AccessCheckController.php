<?php

namespace Drupal\alshaya_geolocation\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 * Geo location controller to prepare data for return pages.
 */
class AccessCheckController extends ControllerBase {

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function accessCheck(AccountInterface $account) {
    // Allow access to the routes only if the store finder status is set.
    return AccessResult::allowedIf($this->config('alshaya_geolocation.settings')->get('geolocation_enabled'));
  }

}
