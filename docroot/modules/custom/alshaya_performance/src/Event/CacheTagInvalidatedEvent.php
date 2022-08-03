<?php

namespace Drupal\alshaya_performance\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class Cache Tag Invalidated Event.
 *
 * @package Drupal\alshaya_performance
 */
class CacheTagInvalidatedEvent extends Event {

  public const PRE_INVALIDATION = 'pre_cache_tag_invalidate';
  public const POST_INVALIDATION = 'post_cache_tag_invalidate';

  /**
   * Tag that is invalidated.
   *
   * @var string
   */
  protected $tag;

  /**
   * CacheTagInvalidatedEvent constructor.
   *
   * @param string $tag
   *   Tag that is invalidated.
   */
  public function __construct(string $tag) {
    $this->tag = $tag;
  }

  /**
   * Get the tag.
   *
   * @return string
   *   Tag that is invalidated.
   */
  public function getTag() {
    return $this->tag;
  }

}
