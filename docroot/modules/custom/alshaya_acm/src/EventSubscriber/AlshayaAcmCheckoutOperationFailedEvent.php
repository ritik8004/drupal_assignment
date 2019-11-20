<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\acq_checkout\Event\AcqCheckoutPaymentFailedEvent;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm\Event\AlshayaAcmPlaceOrderFailedEvent;
use Drupal\alshaya_acm\Event\AlshayaAcmUpdateCartFailedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaAcmCheckoutOperationFailedEvent.
 *
 * @package Drupal\alshaya_acm\EventSubscriber
 */
class AlshayaAcmCheckoutOperationFailedEvent implements EventSubscriberInterface {

  /**
   * Cart Helper.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * AlshayaAcmCheckoutOperationFailedEvent constructor.
   *
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper.
   */
  public function __construct(CartHelper $cart_helper) {
    $this->cartHelper = $cart_helper;
  }

  /**
   * Cancel reservation on payment failure.
   *
   * @param \Drupal\acq_checkout\Event\AcqCheckoutPaymentFailedEvent $event
   *   Event object.
   */
  public function onPaymentFailed(AcqCheckoutPaymentFailedEvent $event) {
    $message = sprintf('Payment Failed for %s, message: %s', $event->getPaymentCode(), $event->getMessage());
    $this->cartHelper->cancelCartReservation($message);
  }

  /**
   * Cancel reservation on payment failure.
   *
   * @param \Drupal\alshaya_acm\Event\AlshayaAcmUpdateCartFailedEvent $event
   *   Event object.
   */
  public function onUpdateCartFailed(AlshayaAcmUpdateCartFailedEvent $event) {
    $this->cartHelper->cancelCartReservation($event->getMessage());
  }

  /**
   * Cancel reservation on payment failure.
   *
   * @param \Drupal\alshaya_acm\Event\AlshayaAcmPlaceOrderFailedEvent $event
   *   Event object.
   */
  public function onPlaceOrderFailed(AlshayaAcmPlaceOrderFailedEvent $event) {
    $this->cartHelper->cancelCartReservation($event->getMessage());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcqCheckoutPaymentFailedEvent::EVENT_NAME][] = ['onPaymentFailed'];
    $events[AlshayaAcmUpdateCartFailedEvent::EVENT_NAME][] = ['onUpdateCartFailed'];
    $events[AlshayaAcmPlaceOrderFailedEvent::EVENT_NAME][] = ['onPlaceOrderFailed'];
    return $events;
  }

}
