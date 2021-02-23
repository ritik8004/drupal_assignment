<?php

namespace App\Event;

/**
 * Class for Cart Data Event.
 *
 * @package App\Event
 */
class CartDataEvent {

  const EVENT_NAME = 'process_cart_data';

  /**
   * Processed cart data.
   *
   * @var array
   */
  private $processedCartData;

  /**
   * Full Cart.
   *
   * @var array
   */
  private $cart;

  /**
   * CartDataEvent constructor.
   *
   * @param array $processedCartData
   *   Processed cart data.
   * @param array $cart
   *   Full cart data.
   */
  public function __construct(array $processedCartData, array $cart) {
    $this->processedCartData = $processedCartData;
    $this->cart = $cart;
  }

  /**
   * Get full cart data.
   *
   * @return array
   *   Full cart data.
   */
  public function getCart() {
    return $this->cart;
  }

  /**
   * Get processed cart data.
   *
   * @return array
   *   Processed cart data.
   */
  public function getProcessedCartData() {
    return $this->processedCartData;
  }

  /**
   * Set processed cart data.
   *
   * @param array $processedCartData
   *   Processed cart data.
   */
  public function setProcessedCartData(array $processedCartData) {
    $this->processedCartData = $processedCartData;
  }

}
