<?php

namespace Drupal\alshaya_security\Session;

use Drupal\Core\Session\SessionManager;
use Symfony\Component\HttpFoundation\Session\SessionUtils;

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

    $name = $this->getName();
    $original = SessionUtils::popSessionCookie($name, $this->getId());
    if ($original) {
      // Add the cookie as per new browser expectations.
      header($original . '; SameSite=None', FALSE);

      // Add the legacy cookie.
      $header = str_replace($name, $name . self::LEGACY_SUFFIX, $original);
      header($header, FALSE);
    }
  }

}
