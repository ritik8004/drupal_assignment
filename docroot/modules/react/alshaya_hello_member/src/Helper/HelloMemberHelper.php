<?php

namespace Drupal\alshaya_hello_member\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;

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
   * Hello Member constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
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
