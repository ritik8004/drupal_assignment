<?php

namespace Drupal\acq_checkout\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AcqCheckoutPaymentFailedEvent.
 *
 * @package Drupal\acq_checkout\Event
 */
class AcqCheckoutPaymentFailedEvent extends Event {

  const EVENT_NAME = 'acq_checkout.payment_failed';

  /**
   * Payment Method code.
   *
   * @var string
   */
  protected $paymentMethod;

  /**
   * Failure reason / message.
   *
   * @var string
   */
  protected $message;

  /**
   * AcqCheckoutPaymentFailedEvent constructor.
   *
   * @param string $payment_method
   *   Payment Method code.
   * @param string $message
   *   Failure reason / message.
   */
  public function __construct(string $payment_method, string $message) {
    $this->paymentMethod = $payment_method;
    $this->message = $message;
  }

  /**
   * Get the payment method for which payment failed.
   *
   * @return string
   *   Payment Method code.
   */
  public function getPaymentCode() {
    return $this->paymentMethod;
  }

  /**
   * Get the failure reason.
   *
   * @return string
   *   Failure reason / message.
   */
  public function getMessage() {
    return $this->message;
  }

}
