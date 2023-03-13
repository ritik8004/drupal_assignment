<?php

namespace Drupal\alshaya_shoeai;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper class for getting shoeai config.
 *
 * @package Drupal\alshaya_shoeai
 */
class AlshayaShoeAi {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Alshaya Constructor.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Check if shoeAI is enabled or not.
   * @return bool
   * Return true when shoeai is enabled.
   */
  public function isShoeAiFeatureEnabled() {
    $shoe_ai_enabled = FALSE;
    $alshaya_shoeai_settings = $this->configFactory->get('alshaya_shoeai.settings');
    $state = $alshaya_shoeai_settings->get('enable_shoeai');
    if ($state) {
      $shoe_ai_enabled = TRUE;
    }
    return $shoe_ai_enabled;
  }

}
