<?php

namespace Drupal\alshaya_security\Session;

use Drupal\Core\Session\SessionConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the default session configuration generator.
 */
class AlshayaSessionConfiguration extends SessionConfiguration {

  /**
   * {@inheritdoc}
   */
  public function hasSession(Request $request) {
    // Here we try to set the legacy cookies we set in AlshayaSessionManager
    // in original expected keys if for some reason they are not available.
    // We do this here as this is invoked before starting the session.
    $cookies = $request->cookies->all();
    foreach ($cookies as $name => $value) {
      if (strpos($name, AlshayaSessionManager::LEGACY_SUFFIX) !== FALSE) {
        $expected = str_replace(AlshayaSessionManager::LEGACY_SUFFIX, '', $name);
        if (empty($cookies[$expected])) {
          $_COOKIE[$expected] = $value;
          $request->cookies->set($expected, $value);

          $options = $this->getOptions($request);

          // Set the cookie back so we have the original cookie back again.
          // @TODO: Change it when moving to PHP 7.3 version.
          // Not doing now as we don't have a way to t\est it.
          $params = session_get_cookie_params();
          $expire = $params['lifetime'] ? REQUEST_TIME + $params['lifetime'] : 0;
          setcookie($expected, $value, $expire, $params['path'], $options['cookie_domain'] . '; SameSite=None', TRUE, $params['httponly']);
        }
      }
    }

    return parent::hasSession($request);
  }

}
