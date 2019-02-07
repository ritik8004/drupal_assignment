<?php

namespace Drupal\alshaya_product_options;

use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\taxonomy\TermInterface;

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
  protected $skuFieldsManager;

  /**
   * I18nHelper object.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  protected $i18nHelper;

  /**
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  protected $productOptionsManager;

  /**
   * Alshaya API Wrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Swatches Helper service object.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  protected $swatches;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  protected $syncedOptions = [];

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
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(SKUFieldsManager $sku_fields_manager,
                              I18nHelper $i18n_helper,
                              ProductOptionsManager $product_options_manager,
                              AlshayaApiWrapper $api_wrapper,
                              SwatchesHelper $swatches,
                              LoggerChannelInterface $logger) {
    $this->skuFieldsManager = $sku_fields_manager;
    $this->i18nHelper = $i18n_helper;
    $this->productOptionsManager = $product_options_manager;
    $this->apiWrapper = $api_wrapper;
    $this->swatches = $swatches;
    $this->logger = $logger;
  }

  /**
   * Synchronize all product options.
   */
  public function synchronizeProductOptions() {
    $this->logger->debug('Sync for all product attribute options started.');
    $fields = $this->skuFieldsManager->getFieldAdditions();

    // We only want to sync attributes.
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
      foreach ($sync_options as $attribute_code) {
        $this->syncProductOption($attribute_code, $langcode);
      }
    }

    // We won't do this cleanup for single sync.
    // All code here is only till the time we get it working through ACM.
    // And single option sync `drush sync-option` is only for testing.
    // On prod we do sync of all options only.
    $this->productOptionsManager->deleteUnavailableOptions($this->syncedOptions);
    $this->logger->debug('Sync for all product attribute options finished.');
  }

  /**
   * Sync specific attribute's options for particular language.
   *
   * @param string $attribute_code
   *   Attribute code.
   * @param string $langcode
   *   Language code.
   */
  public function syncProductOption($attribute_code, $langcode) {
    $this->apiWrapper->updateStoreContext($langcode);

    $this->logger->debug('Sync for product attribute options started of attribute @attribute_code in language @langcode.', [
      '@attribute_code' => $attribute_code,
      '@langcode' => $langcode,
    ]);

    try {
      // First get attribute info.
      $attribute = $this->apiWrapper->getProductAttributeWithSwatches($attribute_code);
      $attribute = json_decode($attribute, TRUE);

      // Dummy data to test product options.
      $attribute = [];
      $attribute['swatches'] = [];
      $attribute['attribute_id'] = '5443';
      $attribute['attribute_code'] = 'size_shoe_eu';
      $attribute['size_chart'] = 1;
      $attribute['size_chart_label'] = 'EU';
      $attribute['size_group'] = 'shoes';
      $attribute['options'] = [
        [
          "label" => "M",
          "value" => "2028",
        ],
      ];
    }
    catch (\Exception $e) {
      // For now we have many fields in sku_base_fields which are not
      // available in all brands.
      return;
    }

    if (empty($attribute) || empty($attribute['options'])) {
      return;
    }

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

      $this->syncedOptions[$attribute_code][$option['value']] = $option['value'];

      // Check if we have value for swatch and it is changed, we trigger
      // save only if value changed.
      if (isset($swatches[$option['value']])) {
        $this->swatches->updateAttributeOptionSwatch($term, $swatches[$option['value']]);
      }

      // Check if we have value for multi size and it is changed, we trigger
      // save only if value changed.
      if (isset($attribute['size_chart'])) {
        $this->updateAttributeOptionSize($term, $attribute);
      }
    }

    $this->logger->debug('Sync for product attribute options finished of attribute @attribute_code in language @langcode.', [
      '@attribute_code' => $attribute_code,
      '@langcode' => $langcode,
    ]);

    $this->apiWrapper->resetStoreContext();
  }

  /**
   * Update Term with Attribute option value if changed.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term.
   * @param array $attributes_info
   *   Attributes info array received from API.
   */
  public function updateAttributeOptionSize(TermInterface $term, array $attributes_info) {
    // Reset current values.
    $term->get('field_attribute_size_chart')->setValue($attributes_info['size_chart']);
    $term->get('field_attribute_size_chart_label')->setValue($attributes_info['size_chart_label']);
    $term->get('field_attribute_size_group')->setValue($attributes_info['size_group']);

    $term->save();
  }

}
