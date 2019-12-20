<?php

namespace Drupal\alshaya_performance\Plugin\views\cache;

use Drupal\alshaya_search_api\EventSubscriber\AlshayaSearchApiProductProcessedEventSubscriber;
use Drupal\Core\Cache\Cache;
use Drupal\search_api\Plugin\views\cache\SearchApiTagCache;

/**
 * Simple caching of query results for Views displays.
 *
 * @ingroup views_cache_plugins
 *
 * @ViewsCache(
 *   id = "alshaya_search_api_tag",
 *   title = @Translation("Alshaya Search API Tag based"),
 *   help = @Translation("Tag based caching of data. Caches will persist until any related cache tags are invalidated. Note: List tags are not added with this plugin")
 * )
 */
class AlshayaSearchApiTagCache extends SearchApiTagCache {

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Alshaya Search API Tag');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // We need to do this only for database index.
    if ($this->getQuery()->getIndex()->id() !== 'product') {
      return parent::getCacheTags();
    }

    $tags = $this->view->storage->getCacheTags();

    // We will invalidate this cache tag later in separate queue / cron.
    $list_tags = [];
    $tids = $this->view->args[0] ?? '';
    foreach (explode('+', $tids) as $tid) {
      $list_tags[] = AlshayaSearchApiProductProcessedEventSubscriber::CACHE_TAG_PREFIX . $tid;
    }

    $tags = Cache::mergeTags($list_tags, $tags);
    return $tags;
  }

}
