<?php

namespace Drupal\acq_sku;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Cart Form Helper.
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

}
