<?php

namespace Drupal\acq_sku\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a acq sku validator event for event listeners.
 */
class AcqSkuValidateEvent extends Event {

  /**
   * Acq sku validate.
   */
  public const ACQ_SKU_VALIDATE = 'acq_sku.validate';

  /**
   * Product data.
   *
   * @var product
   */
  protected $product;

  /**
   * Constructs an acq sku validator event.
   *
   * @param array $product
   *   Array of product attributes.
   */
  public function __construct(array $product) {
    $this->product = $product;
  }

  /**
   * Get the inserted entity.
   *
   * @return array
   *   Get product value.
   */
  public function getProduct() {
    return $this->product;
  }

  /**
   * {@inheritdoc}
   */
  public function setProduct($product) {
    $this->product = $product;
  }

}
