<?php

namespace Drupal\acq_sku\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a acq sku validator event for event listeners.
 */
class AcqSkuValidateEvent extends Event {

  const ACQ_SKU_VALIDATE = 'acq_sku.validate';

  /**
   * Product data being imported.
   */
  protected $product;

  /**
   * Constructs an acq sku validator event.
   *
   * @param array $product
   */
  public function __construct(array $product) {
    $this->product = $product;
  }

  /**
   * Get the inserted entity.
   *
   * @return array
   */
  public function getProduct() {
    return $this->product;
  }

  /**
   * Set the product to event object.
   *
   * @param array $product
   *   Array of product attributes.
   */
  public function setProduct($product) {
    $this->product = $product;
  }

}
