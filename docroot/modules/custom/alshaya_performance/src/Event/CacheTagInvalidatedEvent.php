<?php

namespace Drupal\alshaya_performance\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CacheTagInvalidatedEvent.
 *
 * @package Drupal\alshaya_performance
 */
class CacheTagInvalidatedEvent extends Event {

  const EVENT_NAME = 'cache_tag_invalidated';

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
