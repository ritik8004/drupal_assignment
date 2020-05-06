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
  public function getName(Request $request) {
    $this->setCookieFromLegacy($request);
    return parent::getName($request);
  }

  /**
   * Wrapper function to set original cookies from legacy cookies.
   *
   * Here we try to set the legacy cookies we set in AlshayaSessionManager
   * in original expected keys if for some reason they are not available.
   * We do this here as this is invoked before starting the session.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  protected function setCookieFromLegacy(Request $request) {
    static $processed = NULL;

    if ($processed) {
      return;
    }

    // Set the static variable first to ensure we don't call this function
    // recursively.
    $processed = TRUE;

    $cookies = $request->cookies->all();
    foreach ($cookies as $name => $value) {
      if (strpos($name, AlshayaSessionManager::LEGACY_SUFFIX) !== FALSE) {
        $expected = str_replace(AlshayaSessionManager::LEGACY_SUFFIX, '', $name);
        if (empty($cookies[$expected])) {
          $_COOKIE[$expected] = $value;
          $request->cookies->set($expected, $value);

          $options = $this->getOptions($request);

          // Set the cookie back so we have the original cookie back again.
          // If the user upgrades the browser and tries to checkout without
          // original cookie, we will face the same 500.
          // @TODO: Change it when moving to PHP 7.3 version.Not doing now as
          // we don't have a way to test it.
          $params = session_get_cookie_params();
          $expire = $params['lifetime'] ? REQUEST_TIME + $params['lifetime'] : 0;
          // Compare current php version.
          // We will supports setcookie() with php version <=>php7.3.
          if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
            setcookie($expected, $value, [
              'expires' => $expire,
              'path' => $params['path'],
              'domain' => $options['cookie_domain'],
              'samesite' => 'None',
              'secure' => TRUE,
              'httponly' => $params['httponly'],
            ]);
          }
          else {
            setcookie($expected, $value, $expire, $params['path'], $options['cookie_domain'] . '; SameSite=None', TRUE, $params['httponly']);
          }
        }
      }
    }
  }

}
