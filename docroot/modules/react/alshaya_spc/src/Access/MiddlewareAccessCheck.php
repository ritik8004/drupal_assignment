<?php

namespace Drupal\alshaya_spc\Access;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks access for displaying configuration translation page.
 */
class MiddlewareAccessCheck implements AccessInterface {

  use LoggerChannelTrait;

  /**
   * Check access based on custom header for middleware.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function access(Request $request) {
    $secret = $request->headers->get('alshaya-middleware') ?? '';

    if ($secret !== md5(Settings::get('middleware_auth'))) {
      $this->getLogger('MiddlewareAccessCheck')->warning('Invalid alshaya-middleware header value received : @value', [
        '@value' => $secret,
      ]);

      return AccessResult::forbidden('Invalid alshaya-middleware header value received');
    }

    return AccessResult::allowed();
  }

}
