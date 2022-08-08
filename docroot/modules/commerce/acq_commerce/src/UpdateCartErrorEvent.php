<?php

namespace Drupal\acq_commerce;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class Update Cart Error Event.
 *
 * @package Drupal\acq_commerce
 */
class UpdateCartErrorEvent extends Event {

  public const SUBMIT = 'updateCart.submit.error';

  /**
   * The PHP exception we throw from SKU add to cart forms.
   *
   * @var \Exception
   */
  protected $exception;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Exception $exception) {
    $this->exception = $exception;
  }

  /**
   * Get The exception.
   *
   * @return \Exception
   *   Exception object which contains code and message.
   */
  public function getEventException() {
    return $this->exception;
  }

  /**
   * A Event description method for info.
   */
  public function myEventDescription() {
    return "This event occurs when there is a exception while updating cart.";
  }

}
