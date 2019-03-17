<?php

namespace Drupal\acq_sku;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CartFormHelper.
 *
 * @package Drupal\acq_sku
 */
class CartFormHelper {

  /**
   * Configurable form settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * CartFormHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('acq_sku.configurable_form_settings');
  }

  /**
   * Get the attribute codes with weight for particular attribute set.
   *
   * @param string $attribute_set
   *   Attribute set.
   *
   * @return array
   *   Attribute codes with weight as value.
   */
  public function getConfigurableAttributeWeights($attribute_set = 'default') {
    $attribute_set = strtolower($attribute_set);
    $weights = $this->config->get('attribute_weights');
    $set_weights = $weights[$attribute_set] ?? $weights['default'];
    asort($set_weights);
    return $set_weights;
  }

  /**
   * Check if attribute needs sorting.
   *
   * @param string $attribute_code
   *   Attribute code.
   *
   * @return bool
   *   TRUE if attribute needs to be sorted.
   */
  public function isAttributeSortable($attribute_code) {
    $sortable_options = $this->config->get('sortable_options');
    return in_array($attribute_code, $sortable_options);
  }

  /**
   * Get first attribute code based on weights for particular attribute set.
   *
   * @param string $attribute_set
   *   Attribute set.
   *
   * @return string
   *   First attribute code based on weights for particular attribute set.
   */
  public function getFirstAttribute($attribute_set = 'default') {
    $weights = $this->getConfigurableAttributeWeights($attribute_set);
    $attributes = $weights ? array_keys($weights) : [];
    return !empty($attributes) ? reset($attributes) : '';
  }

  /**
   * Check if we need to show quantity field.
   *
   * @return bool
   *   TRUE if quantity field is to be shown.
   */
  public function showQuantity() {
    return (bool) $this->config->get('show_quantity');
  }

  /**
   * Helper function to get all child SKUs linked with same style code.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Parent SKU entity for which children need to be fetched.
   *
   * @return array
   *   List of child SKU objected keyed by SKU code.
   */
  public function getStyleCodeChildren(SKUInterface $sku) {
    $child_skus = [];

    if ($style_code = $this->getStyleCode($sku)) {
      $query = \Drupal::database()->select('acq_sku_field_data', 'asfd');
      $query->condition('asfd.attr_style_code', $style_code);
      $query->condition('type', 'simple');
      $query->fields('asfd', ['sku']);
      $results = $query->execute()->fetchAll();

      // Load all child SKUs before pushing them into the tree.
      foreach ($results as $result) {
        $child_skus[$result->sku] = SKU::loadFromSku($result->sku);
      }
    }

    return $child_skus;
  }

  /**
   * Fetch additional configurables based on the style code attributes.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Parent SKU entity for which additional configurables need to be fetched.
   * @param array $configurables
   *   Refernece variable to the original set of configurable attributes.
   */
  public function fetchAdditionalConfigurables(SKUInterface $sku, array &$configurables) {
    // @TODO: Figure out a way to identify additional configurable attributes.
    $additional_configurables = [
      'article_castor_id' => [
        'attribute_id' => 143,
        'code' => 'article_castor_id',
        'label' => 'Article Castor Id',
        'position' => 0,
      ],
    ];

    foreach ($additional_configurables as $configurable_code => $configurable) {
      $configurables[] = array_merge($configurable, ['values' => $this->fetchAdditionConfigurableValues($configurable_code, $sku)]);
    }
  }

  /**
   * Fetch values for the configurable attribute from child skus.
   *
   * @param string $configurable_code
   *   Configurable attribute code.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Parent SKU entity for which configurable attribute values are needed.
   *
   * @return array
   *   Array kof attribute value & their labels.
   */
  public function fetchAdditionConfigurableValues($configurable_code, SKUInterface $sku) {
    $child_skus = $this->getStyleCodeChildren($sku);
    $child_sku_attributes = [];

    foreach ($child_skus as $child_sku) {
      if ($child_sku instanceof SKU) {
        $plugin = $sku->getPluginInstance();
        $child_sku_attributes[] = [
          'label' => $plugin->getAttributeValue($child_sku->id(), 'color_label'),
          'value_id' => $plugin->getAttributeValue($child_sku->id(), $configurable_code),
        ];
      }
    }

    return $child_sku_attributes;
  }

  /**
   * Helper function to test if the SKU has style code attribute.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity for which want to test for style code attribute.
   *
   * @return bool|string
   *   Return style code value if the SKU has one, else FALSE.
   */
  public function getStyleCode(SKUInterface $sku) {
    if ($sku->hasField('attr_style_code') &&
      ($style_code = $sku->get('attr_style_code')->getString())) {
      return $style_code;
    }

    return FALSE;
  }

}
