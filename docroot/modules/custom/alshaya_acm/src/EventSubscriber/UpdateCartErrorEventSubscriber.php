<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\acq_commerce\UpdateCartErrorEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Update Cart Error Event Subscriber.
 *
 * @package Drupal\alshaya_acm
 */
class UpdateCartErrorEventSubscriber implements EventSubscriberInterface {

  /**
   * Array to store all error messages.
   *
   * @var array
   */
  public static $errors = [];

  /**
   * Variable to store exception code.
   *
   * @var int
   */
  public static $code = 0;

  /**
   * UpdateCartErrorEventSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('alshaya_acm');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[UpdateCartErrorEvent::SUBMIT][] = ['onUpdateCartError', 800];
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
   * Get error code from static variable.
   *
   * @return int
   *   Returns the error code available in EventSubscriber.
   */
  public static function getCode() {
    return self::$code;
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
   * Adds error code to static variable.
   *
   * @param int $code
   *   Error code to set in static variable.
   */
  public static function setCode($code) {
    if ($code) {
      self::$code = $code;
    }
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
   * @param \Drupal\acq_commerce\UpdateCartErrorEvent $event
   *   Exception raised.
   */
  public function onUpdateCartError(UpdateCartErrorEvent $event) {
    // Get Event Exception.
    $exception = $event->getEventException();

    // Log notice.
    $this->logger->notice($exception->getMessage());

    // Set in static variables.
    self::setCode($exception->getCode());
    self::setError($exception->getMessage());
  }

}
