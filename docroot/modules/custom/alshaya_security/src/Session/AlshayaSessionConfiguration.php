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
  public function getOptions(Request $request) {
    $options = parent::getOptions($request);

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
        }
      }
    }

    return $options;
  }

}
