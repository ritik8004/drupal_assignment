<?php

namespace Drupal\alshaya_egift_card\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper class for Egift Card.
 *
 * @package Drupal\alshaya_egift_card\Helper
 */
class EgiftCardHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The api helper object.
   *
   * @var Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */

    /**
     * EgiftCardHelper constructor.
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
   * Helper to check if EgiftCard is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isEgiftCardEnabled() {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('egift_card_enabled');
  }

  /**
   * Helper to get list of not supported payment methods for eGift card.
   *
   * @return array
   *   An array containting all the payment methods with enable/disable value.
   */
  public function getNotSupportedPaymentMethods() {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('payment_methods_not_supported');
  }

}
