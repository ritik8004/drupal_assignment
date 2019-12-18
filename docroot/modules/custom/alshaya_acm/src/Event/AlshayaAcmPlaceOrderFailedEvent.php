<?php

namespace Drupal\alshaya_acm\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AlshayaAcmPlaceOrderFailedEvent.
 *
 * @package Drupal\acq_checkout\Event
 */
class AlshayaAcmPlaceOrderFailedEvent extends Event {

  const EVENT_NAME = 'alshaya_acm.place_order_failed';

  /**
   * Failure reason / message.
   *
   * @var string
   */
  protected $message;

  /**
   * AlshayaAcmPlaceOrderFailedEvent constructor.
   *
   * @param string $message
   *   Failure reason / message.
   */
  public function __construct(string $message) {
    $this->message = $message;
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
