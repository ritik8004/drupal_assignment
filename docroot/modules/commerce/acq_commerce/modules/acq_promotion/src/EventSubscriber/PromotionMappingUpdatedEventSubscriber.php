<?php

namespace Drupal\acq_promotion\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_promotion\Event\PromotionMappingUpdatedEvent;
use Drupal\acq_sku\Entity\SKU;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Promotion Mapping Updated Event Subscriber.
 */
class PromotionMappingUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[PromotionMappingUpdatedEvent::EVENT_NAME][] = [
      'onPromotionMappingUpdated',
      -100,
    ];
    return $events;
  }

  /**
   * Subscriber callback for the event.
   *
   * @param \Drupal\acq_promotion\Event\PromotionMappingUpdatedEvent $event
   *   Event.
   */
  public function onPromotionMappingUpdated(PromotionMappingUpdatedEvent $event) {
    // Trigger save for all the SKUs to ensure it's caches are invalidated.
    // This will be executed at the end so any custom logic should use higher
    // number and stop propagation so this is not executed.
    foreach ($event->getSkus() as $sku) {
      $entity = SKU::loadFromSku($sku);
      if ($entity instanceof SKUInterface) {
        $entity->save();
      }
    }
  }

}
