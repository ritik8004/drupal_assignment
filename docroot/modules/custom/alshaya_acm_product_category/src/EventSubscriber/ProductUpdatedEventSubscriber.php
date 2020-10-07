<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Updated Event Subscriber.
 *
 * @package Drupal\alshaya_acm_product_category\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Product Category Manager.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryManager
   */
  private $productCategoryManager;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryManager $product_category_manager
   *   Product Category Manager.
   */
  public function __construct(ProductCategoryManager $product_category_manager) {
    $this->productCategoryManager = $product_category_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT][] = [
      'onProductProcessed',
      600,
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
    $this->productCategoryManager->processSalesCategoryCheckForSku($event->getSku());
  }

}
