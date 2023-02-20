<?php

namespace Drupal\alshaya_master\Helper;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableRedirectResponse;

/**
 * Class Non Cached Redirect.
 *
 * @package Drupal\alshaya_master\Helper
 */
class NonCachedRedirect {

  /**
   * Wrapper function to redirect the user.
   *
   * It does all the required operations and adds header to explicitly
   * disable caching of the redirect.
   *
   * @param string $url
   *   URL to redirect to.
   */
  public static function redirect(string $url): never {
    $response = new CacheableRedirectResponse($url);

    $request = \Drupal::request();
    // Save the session so things like messages get saved.
    $request->getSession()->save();
    $response->prepare($request);

    // Make response non-cacheable.
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->setCacheMaxAge(0);
    $response->addCacheableDependency($cacheable_metadata);

    // Make sure to trigger kernel events.
    \Drupal::service('kernel')->terminate($request, $response);

    $response->send();
    exit;
  }

}
