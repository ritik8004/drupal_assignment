<?php

namespace App\Service\CheckoutCom;

use App\Service\Config\SystemSettings;

/**
 * Class for bin validation.
 *
 * @package App\Service\CheckoutCom
 */
class BinValidator {

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * BinValidator constructor.
   *
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(SystemSettings $settings) {
    $this->settings = $settings;
  }

  /**
   * Get bin numbers.
   *
   * @param string $payment_method
   *   The payment method for which we need to get the list of bin.
   *
   * @return array
   *   List of bin numbers.
   */
  public function getBinNumbers($payment_method) {
    $bin_numbers = $this->settings->getSettings('bins')[$payment_method] ?? [];

    return $bin_numbers;
  }

  /**
   * Checks if the given bin is in the list of bins for given payment method.
   *
   * @param string $bin
   *   The card bin to verify.
   * @param string $payment_method
   *   The payment method to which we compare the given bin.
   *
   * @return bool
   *   Return true if matches, false otherwise.
   */
  public function binMatchesPaymentMethod(string $bin, string $payment_method) {
    $bin_numbers = $this->getBinNumbers($payment_method);

    return in_array($bin, $bin_numbers);
  }

}
