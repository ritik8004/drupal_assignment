<?php

namespace Drupal\alshaya_performance\Plugin\QueueWorker;

/**
 * InvalidateCacheTags.
 *
 * @QueueWorker(
 *   id = "alshaya_invalidate_cache_tags_on_priority",
 *   title = @Translation("Alshaya Invalidate Cache Tags in Queue on Priority."),
 * )
 */
class InvalidateCacheTagsOnPriority extends InvalidateCacheTags {

  /**
   * Queue Name.
   */
  public const QUEUE_NAME = 'alshaya_invalidate_cache_tags_on_priority';

}
