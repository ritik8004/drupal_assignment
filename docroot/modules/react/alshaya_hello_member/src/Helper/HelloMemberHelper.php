<?php

namespace Drupal\alshaya_hello_member\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;

/**
 * Helper class for Hello Member.
 *
 * @package Drupal\alshaya_hello_member\Helper
 */
class HelloMemberHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Hello Member constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MobileNumberUtilInterface $mobile_util
  ) {
    $this->configFactory = $config_factory;
    $this->mobileUtil = $mobile_util;
  }

  /**
   * Helper to check if Hello Member is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isHelloMemberEnabled() {
    return $this->getConfig()->get('status');
  }

  /**
   * Helper to check if Aura integration with hello member is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isAuraIntegrationEnabled() {
    return $this->getConfig()->get('aura_integration_status');
  }

  /**
   * Helper to get Cache Tags for Hello member Config.
   *
   * @return string[]
   *   A set of cache tags.
   */
  public function getCacheTags() {
    return $this->getConfig()->getCacheTags();
  }

  /**
   * Get aura config.
   *
   * @return array
   *   AURA form related config.
   */
  public function getAuraFormConfig() {
    $country_code = _alshaya_custom_get_site_level_country_code();
    $country_mobile_code = $this->mobileUtil->getCountryCode($country_code);

    $config = [
      'country_mobile_code' => $country_mobile_code,
      'mobile_maxlength' => $this->configFactory->get('alshaya_master.mobile_number_settings')->get('maxlength'),
      'currency_code' => $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code'),
    ];

    return $config;
  }

  /**
   * Wrapper function to get Hello member Config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Online Returns Config.
   */
  public function getConfig() {
    static $config;

    if (is_null($config)) {
      $config = $this->configFactory->get('alshaya_hello_member.settings');
    }

    return $config;
  }

}
