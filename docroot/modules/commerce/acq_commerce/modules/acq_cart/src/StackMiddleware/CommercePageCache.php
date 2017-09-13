<?php

namespace Drupal\acq_cart\StackMiddleware;

use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\HttpFoundation\Request;

/**
 * Executes the page caching before the main kernel takes over the request.
 */
class CommercePageCache extends PageCache {

  /**
   * {@inheritdoc}
   */
  protected function getCacheId(Request $request) {
    $cid_parts = [
      $request->getUri(),
      $request->getRequestFormat(),
    ];
    return implode(':', $cid_parts);
  }

}
