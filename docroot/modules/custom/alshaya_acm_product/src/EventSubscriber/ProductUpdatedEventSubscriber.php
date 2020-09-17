<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_promotion\Event\PromotionMappingUpdatedEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku_stock\Event\StockUpdatedEvent;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\alshaya_acm_product\Service\ProductQueueUtility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Utility to queue products for processing.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductQueueUtility
   */
  protected $queueUtility;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\Service\ProductQueueUtility $queue_utility
   *   Utility to queue products for processing.
   * @param \Drupal\alshaya_acm_product\Service\ProductProcessedManager $product_processed_manager
   *   Product Processed Manager.
   */
  public function __construct(ProductQueueUtility $queue_utility,
                              ProductProcessedManager $product_processed_manager) {
    $this->queueUtility = $queue_utility;
    $this->productProcessedManager = $product_processed_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductUpdated', 900];
    $events[StockUpdatedEvent::EVENT_NAME][] = ['onStockUpdated', -101];
    $events[PromotionMappingUpdatedEvent::EVENT_NAME][] = ['onPromotionMappingUpdated', 100];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdated(ProductUpdatedEvent $event) {
    if ($event->getOperation() == ProductUpdatedEvent::EVENT_DELETE) {
      $this->productProcessedManager->removeProduct($event->getSku()->getSku());
      return;
    }

    $this->queueProductForProcessing($event->getSku());
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

    $this->queueProductForProcessing($event->getSku());
    $event->stopPropagation();
  }

  /**
   * Subscriber callback for the event.
   *
   * @param \Drupal\acq_promotion\Event\PromotionMappingUpdatedEvent $event
   *   Event.
   */
  public function onPromotionMappingUpdated(PromotionMappingUpdatedEvent $event) {
    // We don't want default ACQ code to run.
    $event->stopPropagation();

    // Queues all the products for processing.
    foreach ($event->getSkus() as $sku) {
      $entity = SKU::loadFromSku($sku);
      if ($entity instanceof SKUInterface) {
        $this->queueProductForProcessing($entity);
      }
    }
  }

  /**
   * Queue product for processing.
   *
   * @param \Drupal\acq_sku\Entity\SKU $entity
   *   SKU entity.
   */
  private function queueProductForProcessing(SKU $entity) {
    $skus = [$entity->getSku()];

    if ($entity->bundle() == 'simple') {
      $parents = $entity->getPluginInstance()->getAllParentIds($entity->getSku());
      if (!empty($parents)) {
        $skus = $parents;

        // If a product is visible at both simple as well as configurable level
        // we need to process for the node attached to simple product too.
        // This is content issue but we handle in code.
        if ($entity->getPluginInstance()->getDisplayNodeId($entity, FALSE)) {
          $skus[] = $entity->getSku();
        }
      }
    }

    // Create multiple items if we have multiple parents.
    foreach ($skus as $sku) {
      $this->queueUtility->queueProduct($sku);
    }
  }

}
