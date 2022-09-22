<?php

namespace Drupal\alshaya_tamara\Helper;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper class for Tamara.
 *
 * @package Drupal\alshaya_tamara\Helper
 */
class AlshayaTamaraHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaTamaraHelper Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Check if the Tamara payment method is enabled on Drupal (not excluded).
   *
   * @param array $build
   *   Build.
   */
  public function isTamaraEnabled(array &$build) {
    // Return FALSE if tamara payment method is excluded from the Checkout page.
    $config = $this->configFactory->get('alshaya_acm_checkout.settings');
    $excludedPaymentMethods = $config->get('exclude_payment_methods');

    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config)
      ->applyTo($build);

    if (in_array('tamara', array_filter($excludedPaymentMethods))) {
      return FALSE;
    }

    // Return TRUE if payment method is available.
    return TRUE;
  }

}
