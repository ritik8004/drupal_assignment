<?php

namespace Drupal\acq_commerce;

use Symfony\Component\EventDispatcher\Event;
use Drupal\acq_commerce\Conductor\RouteException;

/**
 * Class UpdateCartErrorEvent.
 *
 * @package Drupal\acq_commerce
 */
class RouteExceptionEvent extends Event {

  public const SUBMIT = 'acq_commerce.conductor.route_exception';

  /**
   * The PHP exception we throw from the API wrapper.
   *
   * @var \Exception
   */
  protected $exception;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteException $exception) {
    $this->exception = $exception;
  }

  /**
   * Get The exception.
   *
   * @return \Exception
   *   Exception object which contains code and message.
   */
  public function getException() {
    return $this->exception;
  }

}
