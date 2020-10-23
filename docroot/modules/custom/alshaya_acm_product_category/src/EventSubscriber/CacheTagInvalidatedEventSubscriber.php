<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_performance\Event\CacheTagInvalidatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Cache Tag Invalidated Event Subscriber.
 *
 * @package Drupal\alshaya_acm_product_category\EventSubscriber
 */
class CacheTagInvalidatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Product Category Tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  private $productCategoryTree;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product Category Tree.
   */
  public function __construct(ProductCategoryTree $product_category_tree) {
    $this->productCategoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[CacheTagInvalidatedEvent::PRE_INVALIDATION][] = [
      'onPreCacheInvalidationEvent',
      100,
    ];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_performance\Event\CacheTagInvalidatedEvent $event
   *   Event object.
   */
  public function onPreCacheInvalidationEvent(CacheTagInvalidatedEvent $event) {
    if ($event->getTag() === ProductCategoryTree::CACHE_TAG) {
      // Refresh mega menu cache if any category term is updated.
      $this->productCategoryTree->refreshCategoryTreeCache();
    }
  }

}
