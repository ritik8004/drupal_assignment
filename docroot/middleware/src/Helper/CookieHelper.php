<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class CookieHelper.
 *
 * @package App\Helper
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
    return Cookie::create(
      $name,
      $value,
      $expiry,
      '/',
      NULL,
      TRUE,
      TRUE,
      FALSE,
      Cookie::SAMESITE_NONE
    );
  }

}
