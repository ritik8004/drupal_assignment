<?php

namespace Drupal\alshaya_spc\Access;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Checks access for spc pages.
 */
class AlshayaSpcAccess {

  // Middleware session key to get from db.
  const MIDDLEWARE_SESSION_KEY = 'acq_cart_middleware';

  // Session cookie key to check.
  const MIDDLEWARE_COOKIE_KEY = 'PHPSESSID';

  /**
   * Get the cart id for current session.
   *
   * @return array|mixed|null
   *   Return null or array of session data.
   */
  public static function getSessionCartId() {
    $cookies = \Drupal::request()->cookies->all();
    if (empty($cookies[self::MIDDLEWARE_COOKIE_KEY])) {
      return NULL;
    }

    $query = \Drupal::database()->select('sessions')
      ->fields('sessions')
      ->condition('sid', Crypt::hashBase64($cookies[self::MIDDLEWARE_COOKIE_KEY]));
    $result = $query->execute()->fetchAssoc();

    if (empty($result)) {
      return NULL;
    }

    // Get the middleware session key from the record.
    $session_data = array_map(function ($data) {
      return @unserialize($data);
    }, explode('|', $result['session']));

    foreach ($session_data as $session_item) {
      if (isset($session_item[self::MIDDLEWARE_SESSION_KEY])) {
        $session_data = $session_item[self::MIDDLEWARE_SESSION_KEY];
        break;
      }
    }

    return $session_data;
  }

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
    $session_data = self::getSessionCartId();

    if (empty($session_data) || empty($session_data['cart_id'])) {
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
    $session_data = self::getSessionCartId();

    if (empty($session_data) || empty($session_data['cart_id'])) {
      $access = AccessResult::forbidden();
    }
    else {
      $access = AccessResult::allowed();
    }

    return $access->addCacheContexts(['user', 'session']);
  }

}
