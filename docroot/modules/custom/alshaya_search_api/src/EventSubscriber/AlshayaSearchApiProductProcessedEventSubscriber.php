<?php

namespace Drupal\alshaya_search_api\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_search_api\Plugin\QueueWorker\InvalidateCategoryListingCache;
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
      }

      foreach ($new_categories ?? [] as $new_category) {
        $this->queueCategoryForCacheInvalidation($new_category);
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
   * Queue category for cache invalidation.
   *
   * @param string|int $category_id
   *   SKU entity.
   */
  protected function queueCategoryForCacheInvalidation($category_id) {
    $this->queueFactory->get(InvalidateCategoryListingCache::QUEUE_NAME)->createItem($category_id);
  }

}
