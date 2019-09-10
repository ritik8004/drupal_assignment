<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductInfoRequestedBaseEventSubscriber {

  use StringTranslationTrait;

  /**
   * Get description and short description array for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of description and short description.
   */
  protected function getDescription(SKU $sku_entity) {
    $static = &drupal_static(__METHOD__, []);
    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }
    $return = $this->prepareDescription($sku_entity);
    $static[$sku_entity->language()->getId()][$sku_entity->getSku()] = $return;
    return $return;
  }

  /**
   * Prepare description and short description array for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of description and short description.
   */
  protected function prepareDescription(SKU $sku_entity) {
    return [
      'short_desc' => NULL,
      'description' => NULL,
    ];
  }

}
