<?php

namespace Drupal\acq_checkoutcom\EventSubscriber;

use Drupal\acq_commerce\Event\OrderPlacedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Order Placed Event Subscriber.
 *
 * @package Drupal\acq_checkoutcom\EventSubscriber
 */
class OrderPlacedEventSubscriber implements EventSubscriberInterface {

  /**
   * Checkout Helper.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutHelper
   */
  protected $session;

  /**
   * OrderPlacedEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[OrderPlacedEvent::EVENT_NAME][] = ['onOrderPlaced', 100];
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
    $this->session->remove('checkout_com_payment_card_' . $event->getCartId());
  }

}
