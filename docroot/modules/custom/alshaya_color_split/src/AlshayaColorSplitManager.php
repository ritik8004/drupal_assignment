<?php

namespace Drupal\alshaya_color_split;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_product_options\SwatchesHelper;
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
   * Language Manager.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  protected $swatchHelper;

  /**
   * AlshayaColorSplitManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatch_helper
   *   Swatch Helper.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              SwatchesHelper $swatch_helper) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->swatchHelper = $swatch_helper;
  }

  /**
   * Get products in same style group.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product.
   * @param bool $include_oos
   *   Flag to specify if we need to include Out Of Stock products or not.
   *
   * @return \Drupal\acq_sku\Entity\SKU[]
   *   Products in group.
   */
  public function getProductsInStyle(SKU $sku, $include_oos = FALSE) {
    $style_code = $this->fetchStyleCode($sku);
    if (empty($style_code)) {
      return [];
    }

    $static = &drupal_static(__METHOD__, []);
    $static_key = implode(':', [
      $style_code,
      $sku->language()->getId(),
      (int) $include_oos,
    ]);

    $langcode = $sku->language()->getId();

    if (isset($static[$static_key])) {
      return $static[$static_key];
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
      if (!($include_oos) && !($variant->getPluginInstance()->isProductInStock($variant))) {
        continue;
      }

      $static[$static_key][$variant->getSku()] = $this->entityRepository->getTranslationFromContext($variant, $langcode);
    }

    return $static[$static_key];
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
    $attribute = $sku->get('attr_grouping_attributes')->getString();
    return $sku->hasField('attr_' . $attribute) ? $attribute : '';
  }

  /**
   * Wrapper function to alter grouping attribute form item.
   *
   * @param array $configurations
   *   Configurations form item to be altered.
   * @param array $options
   *   Options value.
   * @param string $grouping_attribute
   *   Grouping attribute.
   */
  public function alterGroupAttributeFormItem(array &$configurations, array $options, $grouping_attribute) {
    if ($grouping_attribute) {
      $this->swatchHelper->processSwatchGroupAttribute($configurations, $options, $grouping_attribute);
    }
  }

}
