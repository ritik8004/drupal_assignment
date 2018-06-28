<?php

namespace Drupal\alshaya_product_options;

use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_api\AlshayaApiWrapper;

/**
 * Class ProductOptionsHelper.
 *
 * @package Drupal\alshaya_product_options
 */
class ProductOptionsHelper {

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  private $skuFieldsManager;

  /**
   * I18nHelper object.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  private $productOptionsManager;

  /**
   * Alshaya API Wrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  private $apiWrapper;

  /**
   * Swatches Helper service object.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  private $swatches;

  /**
   * ProductOptionsHelper constructor.
   *
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper service object.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches
   *   Swatches Helper service object.
   */
  public function __construct(SKUFieldsManager $sku_fields_manager,
                              I18nHelper $i18n_helper,
                              ProductOptionsManager $product_options_manager,
                              AlshayaApiWrapper $api_wrapper,
                              SwatchesHelper $swatches) {
    $this->skuFieldsManager = $sku_fields_manager;
    $this->i18nHelper = $i18n_helper;
    $this->productOptionsManager = $product_options_manager;
    $this->apiWrapper = $api_wrapper;
    $this->swatches = $swatches;
  }

  /**
   * Synchronize all product options.
   */
  public function synchronizeProductOptions() {
    $fields = $this->skuFieldsManager->getFieldAdditions();

    // We only want to sync which are in attributes (not in extension).
    $fields = array_filter($fields, function ($field) {
      return ($field['parent'] == 'attributes');
    });

    // For existing live sites we might have source empty.
    array_walk($fields, function (&$field, $field_code) {
      if (empty($field['source'])) {
        $field['source'] = $field_code;
      }
    });

    $sync_options = array_column($fields, 'source');

    foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
      $this->apiWrapper->updateStoreContext($langcode);
      foreach ($sync_options as $attribute_code) {
        // First get attribute info.
        $attribute = $this->apiWrapper->getProductAttributeWithSwatches($attribute_code);

        if (empty($attribute) || empty($attribute['options'])) {
          continue;
        }

        $attribute = json_decode($attribute, TRUE);

        $swatches = [];
        foreach ($attribute['swatches'] as $swatch) {
          $swatches[$swatch['option_id']] = $swatch;
        };

        $weight = 0;
        foreach ($attribute['options'] as $option) {
          if (empty($option['value'])) {
            continue;
          }

          $term = $this->productOptionsManager->createProductOption(
            $langcode,
            $option['value'],
            $option['label'],
            $attribute['attribute_id'],
            $attribute['attribute_code'],
            $weight++
          );

          if (empty($term)) {
            continue;
          }

          // Check if we have value for swatch and it is changed, we trigger
          // save only if value changed.
          if (isset($swatches[$option['value']])) {
            $this->swatches->updateAttributeOptionSwatch($term, $swatches[$option['value']]);
          }
        }
      }
    }

    $this->apiWrapper->resetStoreContext();
  }

}
