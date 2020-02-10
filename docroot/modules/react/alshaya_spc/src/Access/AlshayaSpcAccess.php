<?php

namespace Drupal\alshaya_spc\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Checks access for spc pages.
 */
class AlshayaSpcAccess {

  /**
   * Checks access for the login page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public static function checkLoginAccess(AccountInterface $account) {
    $cart_id = \Drupal::service('alshaya_spc.cookies')->getSessionCartId();

    if (empty($cart_id)) {
      $access = AccessResult::forbidden();
    }
    else {
      $access = AccessResult::allowedIf($account->isAnonymous());
    }

    return $access->addCacheContexts(['user', 'session']);
  }

  /**
   * Check access for the checkout page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   *   The access result.
   */
  public static function checkCheckoutAccess(AccountInterface $account) {
    $cart_id = \Drupal::service('alshaya_spc.cookies')->getSessionCartId();

    if (empty($cart_id)) {
      $access = AccessResult::forbidden();
    }
    else {
      $access = AccessResult::allowed();
    }

    return $access->addCacheContexts(['user', 'session']);
  }

}
