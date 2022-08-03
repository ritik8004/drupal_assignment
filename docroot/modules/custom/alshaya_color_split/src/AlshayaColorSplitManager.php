<?php

namespace Drupal\alshaya_color_split;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_product_options\ProductOptionsHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya Color Split Manager.
 *
 * @package Drupal\alshaya_color_split
 */
class AlshayaColorSplitManager {

  use StringTranslationTrait;

  /**
   * Constant to hold attribute id for the pseudo attribute for product split.
   */
  public const PSEUDO_COLOR_ATTRIBUTE_CODE = 99999;

  /**
   * Constant for RGB swatch display type.
   */
  public const PDP_SWATCH_RGB = 'RGB';

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
   * Production Options Helper service object.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  protected $productOptionsHelper;

  /**
   * Language Manager.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  protected $swatchHelper;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaColorSplitManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $product_options_helper
   *   Production Options Manager.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatch_helper
   *   Swatch Helper.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ProductOptionsHelper $product_options_helper,
                              SwatchesHelper $swatch_helper,
                              RequestStack $request_stack) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->productOptionsHelper = $product_options_helper;
    $this->swatchHelper = $swatch_helper;
    $this->currentRequest = $request_stack->getCurrentRequest();
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

    if ($sku->bundle() === 'simple') {
      $parent = $sku->getPluginInstance()->getParentSku($sku);

      // If no parent available, we are grouping simple products.
      $configurable_attribute_codes = $parent instanceof SKU
        ? Configurable::getConfigurableAttributeCodes($parent)
        : [];
    }
    else {
      $configurable_attribute_codes = Configurable::getConfigurableAttributeCodes($sku);
    }

    $attribute_code_color_id = AlshayaColorSplitConfig::get('attribute_code_color_id');

    // If we we have color already set, we do no further processing.
    if (isset($configurable_attribute_codes[$attribute_code_color_id])) {
      return [];
    }

    $langcode = $sku->language()->getId();

    $static = &drupal_static(__METHOD__, []);
    $static_key = implode(':', [
      $style_code,
      $langcode,
      (int) $include_oos,
      $sku->bundle(),
    ] + $configurable_attribute_codes);

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

      // Ignore configurable products without display node.
      if ($variant->bundle() === 'configurable') {
        $variant_node_id = $variant->getPluginInstance()->getDisplayNodeId($variant);
        if (empty($variant_node_id)) {
          continue;
        }
      }

      $parent = $variant->bundle() === 'simple'
        ? $sku->getPluginInstance()->getParentSku($variant)
        : $variant;

      // Do not merge if configurable attributes are not the same.
      // For simple grouping we have stored empty array in this variable above.
      $parent_attribute_codes = $parent instanceof SKU
        ? Configurable::getConfigurableAttributeCodes($parent)
        : [];

      if ($parent_attribute_codes !== $configurable_attribute_codes) {
        continue;
      }

      $static[$static_key][$variant->getSku()] = SKU::getTranslationFromContext($variant, $langcode);
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
    return $this->skuManager->fetchStyleCode($sku);
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
      foreach ($options as $key => $val) {
        $swatch = $this->getGroupingAttributeSwatchData($val, $grouping_attribute);
        if (!empty($swatch)) {
          switch ($swatch['type']) {
            case SwatchesHelper::SWATCH_TYPE_VISUAL_IMAGE:
              $configurations['#options_attributes'][$key]['swatch-image'] = file_url_transform_relative($swatch['swatch']);
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_COLOR:
              // If swatch type is not an image use rgb color code instead.
              $configurations['#attached']['drupalSettings']['sku_configurable_options_color'][$key] = [
                'display_label' => $val,
                'swatch_type' => self::PDP_SWATCH_RGB,
                'display_value' => $swatch['swatch'],
              ];
              break;

            default:
              continue 2;
          }
        }
      }
    }
  }

  /**
   * Wrapper function to get grouping attribute swatch data.
   *
   * @param string $val
   *   Option value.
   * @param string $grouping_attribute
   *   Grouping attribute.
   *
   * @return array
   *   Swatch data.
   */
  public function getGroupingAttributeSwatchData($val, $grouping_attribute) {
    $option_id = $this->productOptionsHelper->getAttributeOptionId($val, $grouping_attribute);
    $swatch = $this->swatchHelper->getSwatch($grouping_attribute, $option_id);

    return $swatch;
  }

  /**
   * Add swatch data in grouping attribute.
   *
   * @param array $variant
   *   Array of variants.
   * @param string $grouping_attribute
   *   Grouping attribute.
   */
  public function addAttributeSwatchData(array &$variant, $grouping_attribute) {
    foreach ($variant['attributes'] as $key => $attr) {
      if ($attr['key'] === $grouping_attribute) {
        $swatch = $this->getGroupingAttributeSwatchData($attr['value'], $grouping_attribute);
        if (!empty($swatch)) {
          $variant['attributes'][$key]['type'] = $swatch['type'];
          if (SwatchesHelper::SWATCH_TYPE_VISUAL_IMAGE === $swatch['type']) {
            // Absolute url for grouping swatch image for MAPP.
            $swatch['swatch'] = $this->currentRequest->getSchemeAndHttpHost() . $swatch['swatch'];
          }
          $variant['attributes'][$key]['swatch'] = $swatch['swatch'];
        }
      }
    }
  }

  /**
   * Wrapper function to get grouping attribute values.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product.
   *
   * @return array
   *   Grouping attribute values.
   */
  public function getGroupingAttributeValues(SKU $sku) {
    $grouping_attribute = $this->getGroupingAttribute($sku);
    if (!empty($grouping_attribute) && $sku->get('attr_' . $grouping_attribute)->getString()) {

      return [
        'label' => $this->t('color', ['context' => 'configurable_attribute']),
        'value' => $sku->get('attr_' . $grouping_attribute)->getString(),
      ];
    }
    return NULL;
  }

}
