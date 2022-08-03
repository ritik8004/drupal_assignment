<?php

namespace Drupal\alshaya_acm_product\Event;

use Drupal\acq_sku\Entity\SKU;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class Product Updated Event.
 *
 * @package Drupal\alshaya_acm_product
 */
class ProductUpdatedEvent extends Event {

  public const EVENT_NAME = 'product_updated';
  public const EVENT_INSERT = 'insert';
  public const EVENT_UPDATE = 'update';
  public const EVENT_DELETE = 'delete';

  public const PRODUCT_PROCESSED = 'processed';
  public const PRODUCT_PROCESSED_EVENT = 'product_processed_event';

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
