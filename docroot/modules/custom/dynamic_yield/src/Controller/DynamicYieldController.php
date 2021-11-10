<?php

namespace Drupal\dynamic_yield\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Dynamic Yield routes.
 */
class DynamicYieldController extends ControllerBase {

  /**
   * Sets cookie for DY Intelligent tracking.
   *
   * @see https://support.dynamicyield.com/hc/en-us/articles/360007211797-Safari-s-Intelligent-Tracking-Prevention-ITP-Policy-and-DYID-Retention#code-examples-0-4
   */
  public function intelligentTracking(Request $request) {
    $dyid_cookie = $request->cookies->get('_dyid');

    $response = new Response();

    // Double check that _dyid cookie exists and that _dyid_server cookie
    // does not exist.
    if ($dyid_cookie && !$request->cookies->get('_dyid_server')) {
      // Add _dyid_server cookie.
      // The name of the cookie.
      // The value of the cookie.
      // The time the cookie expires.
      // The path on the server in which the cookie will be available on.
      // The domain that the cookie is available to.
      // Whether the cookie is secure.
      $response->headers->setCookie(new Cookie(
        '_dyid_server',
        $dyid_cookie,
        strtotime('+1 year'),
        '/',
        NULL,
        TRUE,
        FALSE
      ));
    }

    return $response;
  }

}
