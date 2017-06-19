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
   * Array to store all error messages.
   *
   * @var array
   */
  public static $errors = [];

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
   *   Returns TRUE if there is any value added in $errors array.
   */
  public static function getErrorStatus() {
    return count(self::$errors) > 0;
  }

  /**
   * Get error messages in EventSubscriber.
   *
   * @return array
   *   Returns the error messages available in EventSubscriber.
   */
  public static function getErrors() {
    return self::$errors;
  }

  /**
   * Adds error message to the static variable when there is an error.
   *
   * @param string $error
   *   Error message to add to static array.
   */
  public static function setError($error) {
    self::$errors[] = $error;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\AddToCartErrorEvent $event
   *   Exception raised.
   */
  public function onAddToCartError(AddToCartErrorEvent $event) {
    // Logs a notice.
    $exception = $event->getEventException();
    \Drupal::logger('alshaya_acm')->notice($exception->getMessage());

    self::setError($exception->getMessage());
  }

}
