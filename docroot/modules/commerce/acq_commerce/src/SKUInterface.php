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
   * Get translated SKU entity.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity for which translated entity is required.
   * @param string|null $langcode
   *   Language code in which SKU Entity is required.
   *
   * @return \Drupal\acq_commerce\SKUInterface
   *   SKU Entity in requested language if available or default.
   */
  public static function getTranslationFromContext(SKUInterface $sku, string $langcode = NULL);

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
