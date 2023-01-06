<?php

namespace Drupal\acq_sku_stock\EventSubscriber;

use Drupal\acq_sku_stock\Event\StockUpdatedEvent;
use Drupal\alshaya_acm_product\Plugin\rest\resource\StockResource;
use Drupal\Core\Cache\Cache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Stock Updated Event Subscriber.
 *
 * @package Drupal\acq_sku_stock\EventSubscriber
 */
class StockUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[StockUpdatedEvent::EVENT_NAME][] = [
      'onStockUpdated',
      -100,
    ];

    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku_stock\Event\StockUpdatedEvent $event
   *   Event object.
   */
  public function onStockUpdated(StockUpdatedEvent $event) {
    // Invalidate caches only if stock status changed or quantity is low.
    if (!($event->isStockStatusChanged() || $event->isLowQuantity())) {
      return;
    }

    // This is last fallback. Any custom event subscriber should use
    // higher priority and stop event propagation to apply smarter logic.
    // Invalidate the SKU and the corresponding stock cache when the stock
    // status or quantity is updated.
    $cache_tags = Cache::mergeTags(
      $event->getSku()->getCacheTagsToInvalidate(),
      [StockResource::CACHE_PREFIX . $event->getSku()->id()],
    );

    Cache::invalidateTags($cache_tags);
  }

}
