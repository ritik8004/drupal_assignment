<?php

namespace Drupal\alshaya_security\Session;

use Drupal\Core\Session\SessionManager;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class AlshayaSessionManager.
 *
 * We override the save method to do custom actions post the session cookie is
 * set. Changes around how the browsers handle cookies when redirecting back
 * from another sites (like cybersource or k-net for us) has forced us to
 * add hacks like below. We set the cookie with SameSite=None for Secure ones
 * and we add them twice to support both new and old browsers.
 *
 * @see https://web.dev/samesite-cookie-recipes/#handling-incompatible-clients
 */
class AlshayaSessionManager extends SessionManager {

  /**
   * Suffix to use for legacy cookie name.
   */
  const LEGACY_SUFFIX = '-legacy';

  /**
   * {@inheritdoc}
   */
  public function save() {
    parent::save();

    $headers_list = headers_list();

    foreach ($headers_list as $header) {
      // Check class comment to understand what we do here.
      if (strpos($header, 'Set-Cookie: ') !== FALSE
        && stripos($header, 'secure') !== FALSE
        && stripos($header, 'samesite') === FALSE) {
        // First remove as we will replace same with additional option.
        header_remove($header);

        // Add the cookie as per new browser expectations.
        header($header . '; SameSite=None', FALSE);

        // Add the legacy cookie.
        $cookie = Cookie::fromString(str_replace('Set-Cookie: ', '', $header));
        $header = str_replace($cookie->getName(), $cookie->getName() . self::LEGACY_SUFFIX, $header);
        header($header, FALSE);
      }
    }
  }

}
