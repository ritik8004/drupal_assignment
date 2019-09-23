<?php

namespace Drupal\alshaya_performance\Plugin\views\cache;

use Drupal\Core\Cache\Cache;
use Drupal\views\Plugin\views\cache\Tag;

/**
 * Simple caching of query results for Views displays.
 *
 * @ingroup views_cache_plugins
 *
 * @ViewsCache(
 *   id = "alshaya_tag",
 *   title = @Translation("Alshaya Tag based"),
 *   help = @Translation("Tag based caching of data. Caches will persist until any related cache tags are invalidated. Note: List tags are not added with this plugin")
 * )
 */
class AlshayaTagCache extends Tag {

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Alshaya Tag');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Add list tag still for empty result views.
    if (empty($this->view->total_rows)) {
      return parent::getCacheTags();
    }

    $tags = $this->view->storage->getCacheTags();
    $tags = Cache::mergeTags($tags, $this->view->getQuery()->getCacheTags());
    return $tags;
  }

}
