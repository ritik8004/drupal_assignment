<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_promotion\Event\PromotionMappingUpdatedEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku_stock\Event\StockUpdatedEvent;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\alshaya_acm_product\Service\ProductQueueUtility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Updated Event Subscriber.
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
   * Product Processed Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductProcessedManager
   */
  protected $productProcessedManager;

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
    $events[StockUpdatedEvent::EVENT_NAME][] = ['onStockUpdated', -99];
    $events[PromotionMappingUpdatedEvent::EVENT_NAME][] = [
      'onPromotionMappingUpdated',
      100,
    ];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdated(ProductUpdatedEvent $event) {
    $this->queueProductForProcessing($event->getSku());

    if ($event->getOperation() == ProductUpdatedEvent::EVENT_DELETE) {
      $this->productProcessedManager->removeProduct($event->getSku()->getSku());
      return;
    }
  }

  /**
   * Subscriber Callback for the stock updated event.
   *
   * @param \Drupal\acq_sku_stock\Event\StockUpdatedEvent $event
   *   Event object.
   */
  public function onStockUpdated(StockUpdatedEvent $event) {
    // Stop the propagation as early as possible.
    $event->stopPropagation();

    // Queue the product if stock status changed or we have low quantity.
    if ($event->isStockStatusChanged() || $event->isLowQuantity()) {
      $this->queueProductForProcessing($event->getSku());
    }
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
    $skus = $event->getSkus() ?? [];
    if (is_array($skus) && !empty($skus)) {
      $this->queueUtility->queueAvailableProductsForSkus($skus);
    }
  }

  /**
   * Queue product for processing.
   *
   * @param \Drupal\acq_sku\Entity\SKU $entity
   *   SKU entity.
   */
  private function queueProductForProcessing(SKU $entity) {
    $nid = $entity->getPluginInstance()->getDisplayNodeId($entity, FALSE);
    $this->queueUtility->queueProduct($entity->getSku(), $nid);

    if ($entity->bundle() == 'simple') {
      $parents = $entity->getPluginInstance()->getAllParentIds($entity->getSku());
      if (!empty($parents)) {
        $this->queueUtility->queueAvailableProductsForSkus($parents);
      }
    }
  }

}
