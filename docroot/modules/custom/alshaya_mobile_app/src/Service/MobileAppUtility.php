<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\node\NodeInterface;

/**
 * Utilty Class.
 */
class MobileAppUtility {

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Utility constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  /**
   * Get Deep link based on give object.
   *
   * @param object $object
   *   Object of node or term or query containing node/term data.
   * @param string $type
   *   (optional) String containing info about data incase of query object.
   *
   * @return object|null
   *   Return deeplink url object.
   */
  public function getDeepLink($object, $type = '') {
    if ($object instanceof TermInterface) {
      $return = NULL;
    }
    elseif ($object instanceof NodeInterface) {
      $return = NULL;
    }
    elseif ($type == 'term') {
      $return = NULL;
    }

    return $return;
  }

}
