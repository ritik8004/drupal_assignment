<?php

namespace Drupal\alshaya_spc\Helper;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Cookie Helper class.
 */
class CookieHelper {

  /**
   * Wrapper function to create cookie with some defaults.
   *
   * @param string $name
   *   Name.
   * @param string|null $value
   *   Value.
   * @param int $expiry
   *   Expiry.
   *
   * @return \Symfony\Component\HttpFoundation\Cookie
   *   Cookie.
   */
  public static function create(string $name, string $value = NULL, $expiry = 0) {
    return new Cookie(
      $name,
      $value,
      $expiry,
      '/',
      NULL,
      TRUE,
      FALSE,
      FALSE,
      Cookie::SAMESITE_NONE
    );
  }

}
