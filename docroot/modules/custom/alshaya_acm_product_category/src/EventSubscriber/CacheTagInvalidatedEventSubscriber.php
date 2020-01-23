<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_performance\Event\CacheTagInvalidatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CacheTagInvalidatedEventSubscriber.
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
    $events[CacheTagInvalidatedEvent::EVENT_NAME][] = ['onEvent', 100];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_performance\Event\CacheTagInvalidatedEvent $event
   *   Event object.
   */
  public function onEvent(CacheTagInvalidatedEvent $event) {
    if ($event->getTag() === ProductCategoryTree::CACHE_TAG) {
      // Refresh mega menu cache if any category term is updated.
      $this->productCategoryTree->refreshCategoryTreeCache();
    }
  }

}
