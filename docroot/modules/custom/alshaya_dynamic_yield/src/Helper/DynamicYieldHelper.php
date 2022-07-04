<?php

namespace Drupal\alshaya_dynamic_yield\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper class for Dynamic yield.
 *
 * @package Drupal\alshaya_dynamic_yield\Helper
 */
class DynamicYieldHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Dynamic yield constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Get dynamic yield config for empty divs.
   *
   * @return array
   *   dynamic yield config.
   */
  public function getDynamicYieldConfig() {
    $alshaya_dynamic_yield_config = $this->configFactory->get('alshaya_dynamic_yield.settings');
    $config = [
      'pdpDivPlaceholderCount' => $alshaya_dynamic_yield_config->get('pdp_div_placeholder_count'),
      'cartDivPlaceholderCount' => $alshaya_dynamic_yield_config->get('cart_div_placeholder_count'),
    ];

    return $config;
  }

}
