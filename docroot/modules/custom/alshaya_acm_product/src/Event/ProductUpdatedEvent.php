<?php

namespace Drupal\alshaya_acm_product\Event;

use Drupal\acq_sku\Entity\SKU;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ProductUpdatedEvent.
 *
 * @package Drupal\alshaya_acm_product
 */
class ProductUpdatedEvent extends Event {

  const EVENT_NAME = 'product_updated';

  /**
   * SKU Entity.
   *
   * @var \Drupal\acq_sku\Entity\SKU
   */
  private $sku;

  /**
   * Operation performed - update, insert, delete.
   *
   * @var string
   */
  private $operation;

  /**
   * ProductUpdatedEvent constructor.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $operation
   *   Operation performed - update, insert, delete.
   */
  public function __construct(SKU $sku, string $operation) {
    $this->sku = $sku;
    $this->operation = $operation;
  }

  /**
   * Get SKU Entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   SKU Entity.
   */
  public function getSku() {
    return $this->sku;
  }

  /**
   * Get performed operation.
   *
   * @return string
   *   Operation performed - update, insert, delete.
   */
  public function getOperation() {
    return $this->operation;
  }

}
