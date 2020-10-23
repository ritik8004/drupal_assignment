<?php

namespace Drupal\alshaya_acm_checkout\EventSubscriber;

use Drupal\acq_commerce\Event\OrderPlacedEvent;
use Drupal\alshaya_acm_checkout\CheckoutHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Order Placed Event Subscriber.
 *
 * @package Drupal\alshaya_acm_checkout\EventSubscriber
 */
class OrderPlacedEventSubscriber implements EventSubscriberInterface {

  /**
   * Checkout Helper.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutHelper
   */
  protected $helper;

  /**
   * OrderPlacedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_checkout\CheckoutHelper $helper
   *   Checkout Helper.
   */
  public function __construct(CheckoutHelper $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[OrderPlacedEvent::EVENT_NAME][] = [
      'onOrderPlaced',
      100,
    ];

    return $events;
  }

  /**
   * Process post order placed.
   *
   * @param \Drupal\acq_commerce\Event\OrderPlacedEvent $event
   *   Event object.
   *
   * @throws \Exception
   */
  public function onOrderPlaced(OrderPlacedEvent $event) {
    $this->helper->processPostOrderPlaced($event->getCartId(), $event->getApiResponse());
  }

}
