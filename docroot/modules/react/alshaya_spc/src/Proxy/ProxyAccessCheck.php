<?php

namespace Drupal\alshaya_spc\Proxy;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Site\Settings;

/**
 * Checks access based on allowed hosts.
 */
class ProxyAccessCheck implements AccessInterface {

  /**
   * Drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * ProxyAccessCheck constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings object.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * Checks access based on allowed hosts.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function access(Request $request) {
    $host = $request->getHost();

    // Get allowed host patterns from settings.
    if ($patterns = $this->settings->get('spc_proxy_host_patterns')) {
      foreach ($patterns as $pattern) {
        if (preg_match("/$pattern/", $host)) {
          return AccessResult::allowed();
        }
      }
    }

    return AccessResult::forbidden('Host not allowed to use proxy.');
  }

}
