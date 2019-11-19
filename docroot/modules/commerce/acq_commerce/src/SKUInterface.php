<?php

namespace Drupal\acq_commerce;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a SKU entity.
 *
 * @ingroup acq_commerce
 */
interface SKUInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get SKU of the entity.
   *
   * @return string
   *   SKU of the entity;
   */
  public function getSku();

  /**
   * Refresh stock for the sku using stock api.
   */
  public function refreshStock();

}
