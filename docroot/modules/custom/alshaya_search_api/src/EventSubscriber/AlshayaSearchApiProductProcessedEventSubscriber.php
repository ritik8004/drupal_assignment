<?php

namespace Drupal\alshaya_search_api\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_performance\Plugin\QueueWorker\InvalidateCacheTags;
use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\QueueFactory;
use Drupal\node\NodeInterface;
use Drupal\search_api\Plugin\search_api\datasource\ContentEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaSearchApiProductProcessedEventSubscriber.
 *
 * @package Drupal\alshaya_search_api\EventSubscriber
 */
class AlshayaSearchApiProductProcessedEventSubscriber implements EventSubscriberInterface {

  /**
   * Prefix for custom cache tag on listing pages.
   */
  const CACHE_TAG_PREFIX = 'search_api_list:term:';

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * AlshayaSearchApiProductProcessedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(SkuManager $sku_manager,
                              QueueFactory $queue_factory,
                              Connection $connection) {
    $this->skuManager = $sku_manager;
    $this->queueFactory = $queue_factory;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT][] = ['onProductProcessed', 400];
    return $events;
  }

  /**
   * Subscriber Callback for the product processed event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductProcessed(ProductUpdatedEvent $event) {
    $entity = $event->getSku();
    $node = $this->skuManager->getDisplayNode($entity);

    if ($node instanceof NodeInterface) {
      $item_id = 'entity:node/' . $node->id() . ':' . $node->language()->getId();

      // Note: This will stop working as soon as we move away from
      // Search API Database. This seems at-least somewhat far in future so
      // going ahead with it. We could even invoke Algolia OR Solr Query here.
      // Load the categories currently indexed for this product.
      $indexed_category_ids = $this->getIndexedCategoryIds($item_id);
      $current_category_ids = array_column($node->get('field_category')->getValue(), 'target_id');
      $new_categories = array_diff($current_category_ids, $indexed_category_ids);

      $indexes = ContentEntity::getIndexesForEntity($node);
      foreach ($indexes as $index) {
        $items = $index->loadItemsMultiple([$item_id]);
        $index->indexSpecificItems($items);

        // For search page, invalidate cache if we had nothing in old and
        // something now in new. Ideally this means this product is new.
        // For Algolia it shouldn't matter, we will get data directly from
        // Algolia, we do here for SOLR.
        if ($index->id() === 'acquia_search_index' && empty($indexed_category_ids) && !empty($current_category_ids)) {
          $this->queueCacheInvalidation('search_api_list:acquia_search_index');
        }
      }

      // For listing pages, if we have new categories - add them for
      // cache invalidation.
      foreach ($new_categories ?? [] as $new_category) {
        $this->queueCacheInvalidation(self::CACHE_TAG_PREFIX . $new_category);
      }
    }
  }

  /**
   * Wrapper function to get indexed category ids from database.
   *
   * @param string $item_id
   *   Item ID.
   *
   * @return array
   *   Category IDs indexed for the item.
   */
  protected function getIndexedCategoryIds(string $item_id): array {
    $query = $this->connection->select('search_api_db_product_field_category');
    $query->addField('search_api_db_product_field_category', 'value');
    $query->condition('item_id', $item_id);
    $category_ids = $query->execute()->fetchAllKeyed(0, 0);
    return is_array($category_ids) ? $category_ids : [];
  }

  /**
   * Queue for cache invalidation.
   *
   * @param string $tag
   *   Tag.
   */
  protected function queueCacheInvalidation(string $tag) {
    $this->queueFactory->get(InvalidateCacheTags::QUEUE_NAME)->createItem($tag);
  }

}
