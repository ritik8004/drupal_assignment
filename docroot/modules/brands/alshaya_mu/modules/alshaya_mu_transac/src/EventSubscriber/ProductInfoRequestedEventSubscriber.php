<?php

namespace Drupal\alshaya_mu_transac\EventSubscriber;

use Drupal\acq_sku\ProductInfoRequestedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Contains Product Info Requested Event Subscriber methods.
 *
 * @package Drupal\alshaya_mu_transac\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ProductInfoRequestedEvent::EVENT_NAME => ['onProductInfoRequested'],
    ];
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function onProductInfoRequested(ProductInfoRequestedEvent $event) {
    switch ($event->getFieldCode()) {
      case 'description':
        $this->processDescription($event);
        break;
    }
  }

  /**
   * Process description for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $description = _alshaya_mu_transac_get_product_description($sku_entity);
    $event->setValue($description['description']);
  }

}
