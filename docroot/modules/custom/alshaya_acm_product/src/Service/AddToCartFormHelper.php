<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuFieldsHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_config\AlshayaArrayUtils;
use Drupal\alshaya_product_options\ProductOptionsHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Add To Cart Form Helper.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class AddToCartFormHelper {

  use StringTranslationTrait;

  /**
   * Sku Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $imagesManager;

  /**
   * Fields Helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuFieldsHelper
   */
  private $fieldsHelper;

  /**
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  private $optionsHelper;

  /**
   * Array Utils.
   *
   * @var \Drupal\alshaya_config\AlshayaArrayUtils
   */
  private $arrayUtils;

  /**
   * AddToCartFormHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $images_manager
   *   Images Manager.
   * @param \Drupal\alshaya_acm_product\SkuFieldsHelper $sku_fields_helper
   *   Fields Helper.
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $options_helper
   *   Product Options Helper.
   * @param \Drupal\alshaya_config\AlshayaArrayUtils $alshaya_array_utils
   *   Array Utils.
   */
  public function __construct(SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              SkuFieldsHelper $sku_fields_helper,
                              ProductOptionsHelper $options_helper,
                              AlshayaArrayUtils $alshaya_array_utils) {
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->fieldsHelper = $sku_fields_helper;
    $this->optionsHelper = $options_helper;
    $this->arrayUtils = $alshaya_array_utils;
  }

  /**
   * Alter the configurable form item as per custom needs.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param array $configurable
   *   Configurable form item to be altered.
   * @param bool $swatch_processed
   *   If swatch is already processed for the form or not.
   */
  public function alterConfigurableFormItem(SKU $sku, array &$configurable, bool &$swatch_processed) {
    $key = $configurable['#code'];
    $configurable['#attributes']['data-configurable-code'] = $key;

    $configurable['#options_attributes'] ??= [];
    if (isset($configurable['#type']) && $configurable['#type'] == 'select') {
      $overridden_label = $this->fieldsHelper->getOverriddenAttributeLabel($key);

      $configurable['#empty_value'] = '';
      $configurable['#options_attributes']['']['disabled'] = 'disabled';
      $configurable['#empty_option'] = $overridden_label ?? $this->t('Select @title', ['@title' => $configurable['#title']]);
      $configurable['#attributes']['data-default-title'] = $overridden_label ?? $configurable['#title'];
      $configurable['#title'] = $overridden_label ?? $configurable['#title'];

      $overridden_selected_label = $this->fieldsHelper->getOverriddenAttributeSelectedLabel($key);
      $configurable['#attributes']['data-selected-title'] = $overridden_selected_label ?? $configurable['#attributes']['data-default-title'];

      $overridden_error = $this->fieldsHelper->getOverriddenAttributeError($key);
      if ($overridden_error) {
        $configurable['#required_error'] = $overridden_error;
      }

      // Process and remove not required options first.
      $this->skuManager->processAttribute($configurable);

      if (!$swatch_processed && in_array($key, $this->skuManager->getPdpSwatchAttributes())) {
        $swatch_processed = TRUE;
        $configurable['#attributes']['class'][] = 'form-item-configurable-swatch';

        foreach ($configurable['#options'] as $value => $label) {
          if (empty($value)) {
            continue;
          }

          $swatch_sku = $this->skuManager->getChildSkuFromAttribute($sku, $key, $value);

          if ($swatch_sku instanceof SKU) {
            $swatch_image_url = $this->imagesManager->getPdpSwatchImageUrl($swatch_sku);
            if ($swatch_image_url) {
              $configurable['#options_attributes'][$value]['swatch-image'] = file_url_transform_relative($swatch_image_url);
            }
          }
        }
      }
      elseif ($alternates = $this->optionsHelper->getSizeGroup($key)) {
        $configurable['#attributes']['class'][] = 'form-item-configurable-select-group';
        $combinations = $this->skuManager->getConfigurableCombinations($sku);
        foreach ($configurable['#options'] as $value => $label) {
          $group_data = [];
          foreach ($combinations['attribute_sku'][$key][$value] ?? [] as $child_sku_code) {
            $child_sku = SKU::loadFromSku($child_sku_code, $sku->language()->getId());

            if (!($child_sku instanceof SKU)) {
              continue;
            }

            // Get all alternate labels from child sku.
            foreach ($alternates as $alternate => $alternate_label) {
              $attribute_code = 'attr_' . $alternate;
              $group_data[$alternate] = [
                'label' => $alternate_label,
                'value' => $child_sku->get($attribute_code)->getString(),
              ];
            }
          }

          $configurable['#options_attributes'][$value]['group-data'] = json_encode($group_data);
        }
      }
      else {
        $configurable['#attributes']['class'][] = 'form-item-configurable-select';
      }
    }
  }

  /**
   * Wrapper function to add by_attribute and bySku in combinations.
   *
   * @param array $combinations
   *   Combinations to update.
   * @param array $attributes
   *   Attributes for which combinations need to be updated.
   */
  public function updateCombinations(array &$combinations, array $attributes) {
    // Prepare combinations array grouped by attributes to check later which
    // combination is possible using isset().
    $combinations['by_attribute'] = [];

    $all_combinations = $this->arrayUtils->getAllCombinations($attributes);
    foreach ($combinations['by_sku'] ?? [] as $sku => $combination) {
      foreach ($all_combinations as $possible_combination) {
        $combination_string = '';
        foreach ($possible_combination as $code) {
          $combination_string .= $code . '|' . $combination[$code] . '||';
          $combinations['by_attribute'][$combination_string] = '';
        }
        $combinations['by_attribute'][$combination_string] = $sku;
      }
    }

    $combinations['bySku'] = [];
    foreach ($combinations['by_sku'] as $options) {
      foreach ($options as $code => $value) {
        foreach ($options as $code2 => $value2) {
          if ($code == $code2) {
            continue;
          }
          $combinations['bySku'][$code][$value][$code2][] = $value2;
        }
        unset($options[$code]);
      }
    }
  }

}
