<?php

namespace Drupal\alshaya_pb_transac\EventSubscriber;

use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\alshaya_pb_transac\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      800,
    ];

    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function onProductInfoRequested(ProductInfoRequestedEvent $event) {
    // PB doesn't require context, we use same title for all context.
    switch ($event->getFieldCode()) {
      case 'title':
        $this->processTitle($event);
        break;
    }
  }

  /**
   * Process title for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processTitle(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();
    $title = $event->getValue();

    $group_name = $sku->get('attr_group_name')->getString();
    $sku_name = $sku->get('attr_sku_name')->getString();

    if ($group_name && $sku_name) {
      $title = $this->t('@group_name/@sku_name', [
        '@group_name' => $group_name,
        '@sku_name' => $sku_name,
      ]);
    }
    elseif ($group_name) {
      $title = $group_name;
    }
    elseif ($sku_name) {
      $title = $sku_name;
    }

    $event->setValue($title);
  }

}
