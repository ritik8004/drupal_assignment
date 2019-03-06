<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\acq_sku\AddToCartErrorEvent;
use Drupal\alshaya_acm\CartHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddToCartErrorEventSubscriber.
 *
 * @package Drupal\alshaya_acm\EventSubscriber
 */
class AddToCartErrorEventSubscriber implements EventSubscriberInterface {

  use MessengerTrait;
  use StringTranslationTrait;

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
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Cart Helper.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  private $cartHelper;

  /**
   * AddToCartErrorEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(CartHelper $cart_helper,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->cartHelper = $cart_helper;
    $this->logger = $logger_factory->get('alshaya_acm');
  }

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
   * @param \Drupal\acq_sku\AddToCartErrorEvent $event
   *   Exception raised.
   */
  public function onAddToCartError(AddToCartErrorEvent $event) {
    // Get Event Exception.
    $exception = $event->getEventException();

    $message = $exception->getMessage();

    // Log notice.
    $this->logger->notice($exception->getMessage());

    if (_alshaya_acm_is_out_of_stock_exception($exception)) {
      $this->cartHelper->refreshStockForProductsInCart();

      // Try to remove again (only once) after removing OOS items.
      if ($this->cartHelper->removeOutOfStockItemsFromCart()) {
        try {
          $this->cartHelper->updateCartWrapper(__METHOD__);

          // Operation was successful after second try, show the error message
          // for user to know about the updates user didn't ask for.
          $message = $this->t('Sorry, one or more products in your basket are no longer available and have been removed in order to proceed.');
        }
        catch (\Exception $e) {
          // Do nothing after second try to update cart.
        }
      }
    }

    // Set in static variables.
    self::setCode($exception->getCode());
    self::setError($message);
  }

}
