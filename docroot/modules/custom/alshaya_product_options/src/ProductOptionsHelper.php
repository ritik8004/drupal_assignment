<?php

namespace Drupal\alshaya_product_options;

use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ProductOptionsHelper.
 *
 * @package Drupal\alshaya_product_options
 */
class ProductOptionsHelper {

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper service object.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches
   *   Swatches Helper service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              I18nHelper $i18n_helper,
                              ProductOptionsManager $product_options_manager,
                              AlshayaApiWrapper $api_wrapper,
                              SwatchesHelper $swatches) {
    $this->configFactory = $config_factory;
    $this->i18nHelper = $i18n_helper;
    $this->productOptionsManager = $product_options_manager;
    $this->apiWrapper = $api_wrapper;
    $this->swatches = $swatches;
  }

  /**
   * Synchronize all product options.
   */
  public function synchronizeProductOptions() {
    $sync_options = $this->configFactory->get('alshaya_product_options.settings')->get('sync_options');

    if (empty($sync_options)) {
      return $this->productOptionsManager->synchronizeProductOptions();
    }

    foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
      $this->apiWrapper->updateStoreContext($langcode);
      foreach ($sync_options as $attribute_code) {
        // First get attribute info.
        $attribute = $this->apiWrapper->getProductAttributeWithSwatches($attribute_code);

        if (empty($attribute)) {
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
