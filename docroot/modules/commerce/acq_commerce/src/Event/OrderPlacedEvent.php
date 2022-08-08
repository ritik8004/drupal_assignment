<?php

namespace Drupal\acq_commerce\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class Order Placed Event.
 *
 * @package Drupal\acq_commerce\Event
 */
class OrderPlacedEvent extends Event {

  public const EVENT_NAME = 'order_placed';

  /**
   * API Response.
   *
   * @var array
   */
  private $response;

  /**
   * Get Cart ID.
   *
   * @var int|string
   */
  private $cartId;

  /**
   * OrderPlacedEvent constructor.
   *
   * @param array $response
   *   API Response.
   * @param string|int $cart_id
   *   Cart ID.
   */
  public function __construct(array $response, $cart_id) {
    $this->response = $response;
    $this->cartId = $cart_id;
  }

  /**
   * Get API response.
   *
   * @return array
   *   API Response.
   */
  public function getApiResponse() {
    return $this->response;
  }

  /**
   * Get Cart ID.
   *
   * @return int|string
   *   Cart ID.
   */
  public function getCartId() {
    return $this->cartId;
  }

}
