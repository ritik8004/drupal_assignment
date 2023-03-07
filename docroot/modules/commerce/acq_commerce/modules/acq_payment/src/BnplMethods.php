<?php

namespace Drupal\acq_payment;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides the BNPL Payment Methods.
 */
class BnplMethods {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * BnplMethods constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
  ) {
    $this->configFactory = $config_factory;
  }

  /**
   * Get BNPL payment methods.
   *
   * @return array
   *   An array containing all the BNPL payment methods as set in the config.
   */
  public function getBnplPaymentMethods() {

    return explode(',', $this->configFactory->get('acq_payment.bnpl_payment_config')->get('bnpl_payment_methods'));
  }

}
