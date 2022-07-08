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
    return $this->configFactory->get('alshaya_hello_member.settings')->get('enabled');
  }

  public function ageHelloMember() {
    return $this->configFactory->get('alshaya_hello_member.settings')->get('minimum_age');
  }

}
