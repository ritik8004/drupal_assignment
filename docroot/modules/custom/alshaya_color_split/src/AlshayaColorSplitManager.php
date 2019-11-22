<?php

namespace Drupal\alshaya_color_split;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaColorSplitManager.
 *
 * @package Drupal\alshaya_color_split
 */
class AlshayaColorSplitManager {

  /**
   * Constant to hold attribute id for the pseudo attribute for product split.
   */
  const PSEUDO_COLOR_ATTRIBUTE_CODE = 99999;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaColorSplitManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;

  }

  /**
   * Get products in same style group.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product.
   *
   * @return \Drupal\acq_sku\Entity\SKU[]
   *   Products in group.
   */
  public function getProductsInStyle(SKU $sku) {
    $style_code = $this->fetchStyleCode($sku);
    if (empty($style_code)) {
      return [];
    }

    $static = &drupal_static(__METHOD__, []);
    $langcode = $sku->language()->getId();

    if (isset($static[$style_code][$langcode])) {
      return $static[$style_code][$langcode];
    }

    /** @var \Drupal\acq_sku\Entity\SKU[] $variants */
    $variants = \Drupal::entityTypeManager()
      ->getStorage('acq_sku')
      ->loadByProperties([
        'attr_style_code' => $style_code,
        'type' => $sku->bundle(),
      ]);

    foreach ($variants as $variant) {
      // Skip OOS variants.
      if (!($variant->getPluginInstance()->isProductInStock($variant))) {
        continue;
      }

      $static[$style_code][$langcode][$variant->getSku()] = $this->entityRepository->getTranslationFromContext($variant, $langcode);
    }

    return $static[$style_code][$langcode];
  }

  /**
   * Helper function to check if SKU entity has style code attribute or not.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity for which the style code needs to be fetched.
   *
   * @return bool|mixed
   *   Style code value if field & value exist, FALSE otherwise.
   */
  public function fetchStyleCode(SKU $sku) {
    if ($sku->hasField('attr_style_code') &&
      $style_code = $sku->get('attr_style_code')->getString()) {
      return $style_code;
    }

    return FALSE;
  }

  /**
   * Wrapper function to get grouping attribute.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product.
   *
   * @return string
   *   Grouping attribute.
   */
  public function getGroupingAttribute(SKU $sku) {
    return $sku->get('attr_grouping_attributes')->getString();
  }

}
