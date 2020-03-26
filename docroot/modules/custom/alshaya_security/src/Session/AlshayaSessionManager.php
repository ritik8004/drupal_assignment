<?php

namespace Drupal\alshaya_security\Session;

use Drupal\Core\Session\SessionManager;

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
   * {@inheritdoc}
   */
  public function save() {
    parent::save();

    $headers_list = headers_list();

    foreach ($headers_list as $header) {
      // Check class comment to understand what we do here.
      if (stripos($header, 'secure') !== FALSE && stripos($header, 'samesite') === FALSE) {
        header_remove($header);
        header($header . ';SameSite=None;', FALSE);
        header($header);
      }
    }
  }

}
