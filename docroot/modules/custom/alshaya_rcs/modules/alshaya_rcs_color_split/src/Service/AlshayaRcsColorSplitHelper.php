<?php

namespace Drupal\alshaya_rcs_color_split\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Contains helper methods alshaya_rcs_color_split.
 *
 * @package Drupal\alshaya_rcs_color_split\Services
 */
class AlshayaRcsColorSplitHelper {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs the AlshayaRcsColorSplitHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the product display settings.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The product display settings.
   */
  private function getProductDisplaySettings() {
    static $display_settings;
    if ($display_settings) {
      return $display_settings;
    }

    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    return $display_settings;
  }

  /**
   * Returns the config if multiple color attributes are supported.
   *
   * @return bool
   *   Returns true if multiple color attributes are set, else false.
   */
  public function isSupportsMultipleColorAttributes() {
    $display_settings = $this->getProductDisplaySettings();
    $color_attribute_config = $display_settings->get('color_attribute_config');

    return $color_attribute_config['support_multiple_attributes'];
  }

  /**
   * Gets the color code attribute for products.
   *
   * @return string|bool
   *   Color code attribute if set else false.
   */
  public function getColorCodeAttribute() {
    $display_settings = $this->getProductDisplaySettings();
    $color_attribute_config = $display_settings->get('color_attribute_config');
    if (!$color_attribute_config['support_multiple_attributes']) {
      return FALSE;
    }

    // Get hex value of color.
    $color_code_attribute = $color_attribute_config['configurable_color_code_attribute'];
    // Remove the "attr_" prefix.
    $color_code_attribute = str_replace('attr_', '', $color_code_attribute);

    return $color_code_attribute;
  }

  /**
   * Gets the color attribute.
   *
   * @return string|bool
   *   Color attribute config if found, else false.
   */
  public function getColorAttribute() {
    $display_settings = $this->getProductDisplaySettings();
    $color_attribute_config = $display_settings->get('color_attribute_config');
    if (!$color_attribute_config['support_multiple_attributes']) {
      return FALSE;
    }

    // Get hex value of color.
    $color_attribute = $color_attribute_config['configurable_color_attribute'];
    return $color_attribute;
  }

}
