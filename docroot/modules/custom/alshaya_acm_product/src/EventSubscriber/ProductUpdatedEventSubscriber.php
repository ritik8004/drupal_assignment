<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku_stock\Event\StockUpdatedEvent;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\Plugin\QueueWorker\ProcessProduct;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   */
  public function __construct(QueueFactory $queue_factory) {
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductUpdated', 900];
    $events[StockUpdatedEvent::EVENT_NAME][] = ['onStockUpdated', -101];
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
      }
    }

    // Create multiple items if we have multiple parents.
    foreach ($skus as $sku) {
      $this->queueFactory->get(ProcessProduct::QUEUE_NAME)->createItem($sku);
    }
  }

}
