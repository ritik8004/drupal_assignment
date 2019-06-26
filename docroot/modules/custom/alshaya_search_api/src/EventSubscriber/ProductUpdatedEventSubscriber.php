<?php

namespace Drupal\alshaya_search_api\EventSubscriber;

use Drupal\acq_sku_stock\Event\StockUpdatedEvent;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_search_api\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   */
  public function __construct(SkuManager $sku_manager) {
    $this->skuManager = $sku_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductUpdated', 400];
    $events[StockUpdatedEvent::EVENT_NAME][] = ['onStockUpdated', -101];
    return $events;
  }

  /**
   * Subscriber Callback for the product updated event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdated(ProductUpdatedEvent $event) {
    $entity = $event->getSku();
    $node = $this->skuManager->getDisplayNode($entity);
    if ($node instanceof NodeInterface) {
      $node->original = clone $node;

      // Mark node for reindexing on add/update/delete of SKUs.
      search_api_entity_update($node);
    }
  }

  /**
   * Subscriber Callback for the stock updated event.
   *
   * @param \Drupal\acq_sku_stock\Event\StockUpdatedEvent $event
   *   Event object.
   */
  public function onStockUpdated(StockUpdatedEvent $event) {
    // Do nothing if stock status not changed.
    if (!$event->isStockStatusChanged()) {
      return;
    }

    $entity = $event->getSku();
    $node = $this->skuManager->getDisplayNode($entity);
    if ($node instanceof NodeInterface) {
      $node->original = clone $node;

      // Mark node for reindexing on update of stock.
      search_api_entity_update($node);
    }
  }

}
