<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\acq_sku\AddToCartErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddToCartErrorEventSubscriber.
 *
 * @package Drupal\alshaya_acm\EventSubscriber
 */
class AddToCartErrorEventSubscriber implements EventSubscriberInterface {

  /**
   * Flag to indicate whether we have an error.
   *
   * @var bool
   */
  public static $erroredOnAddToCart = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AddToCartErrorEvent::SUBMIT][] = ['onAddToCartError', 800];
    return $events;
  }

  /**
   * Check error status in EventSubscriber.
   *
   * @return bool
   *   Returns the current value of $erroredOnAddToCart.
   */
  public static function getErrorStatus() {
    return self::$erroredOnAddToCart;
  }

  /**
   * Sets the static variable when there is an error.
   */
  public static function setErrorStatus() {
    self::$erroredOnAddToCart = TRUE;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\AddToCartErrorEvent $event
   *   Exception raised.
   */
  public function onAddToCartError(AddToCartErrorEvent $event) {
    // Set our static variable to TRUE.
    self::setErrorStatus();

    // Logs a notice.
    $exception = $event->getEventException();
    \Drupal::logger('acq_sku')->notice($exception->getMessage());
  }

}
