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
   * Checks access for the form page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public static function checkAccess(AccountInterface $account) {
    $cookies = \Drupal::request()->cookies->all();
    if (empty($cookies[self::MIDDLEWARE_COOKIE_KEY])) {
      return AccessResult::forbidden();
    }

    $query = \Drupal::database()->select('sessions')
      ->fields('sessions')
      ->condition('sid', Crypt::hashBase64($cookies[self::MIDDLEWARE_COOKIE_KEY]));
    $result = $query->execute()->fetchAssoc();

    if (empty($result)) {
      return AccessResult::forbidden();
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

    if (empty($session_data)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf($account->isAnonymous());
  }

}
