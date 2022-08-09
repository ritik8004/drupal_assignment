<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_hm_images\Services\HmImagesHelper;

/**
 * Class Product Updated Event Subscriber.
 *
 * @package Drupal\alshaya_hm_images\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Images Helper Service.
   *
   * @var \Drupal\alshaya_hm_images\Services\HmImagesHelper
   */
  private $imagesHelper;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_hm_images\Services\HmImagesHelper $images_helper
   *   Images Helper Service.
   */
  public function __construct(HmImagesHelper $images_helper) {
    $this->imagesHelper = $images_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT][] = [
      'onProductProcessed',
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
  public function onProductProcessed(ProductUpdatedEvent $event) {
    // Get the color skus and store the same in cache.
    $this->imagesHelper->getColorsForSku($event->getSku());
  }

}
