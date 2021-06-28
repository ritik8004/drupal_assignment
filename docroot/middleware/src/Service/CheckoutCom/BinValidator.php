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
   * Checks if the given bin is valid.
   *
   * @param string $bin
   *   The card bin to verify.
   *
   * @return bool
   *   Return true if valid, false otherwise.
   */
  public function isValidBin(string $bin) {
    $country_code = $this->settings->getSettings('alshaya_site_country_code')['country_code'];
    $validBins = !empty($country_code) ? $this->settings->getSettings('valid_bins')[$country_code] : '';

    return in_array($bin, $validBins);
  }

  /**
   * Error message for bin validation.
   *
   * @return string
   *   Return Error message.
   */
  public function getBinValidationErrorMessage() {
    $country_code = $this->settings->getSettings('alshaya_site_country_code')['country_code'];

    if ($country_code === 'kw') {
      return 'Your card details are valid for K-NET. Please select K-NET as a payment method or enter different credit/debit card details to proceed.';
    }

    if ($country_code === 'qa') {
      return 'Your card details are valid for NAPS Debit Card. Please select NAPS Debit Card as a payment method or enter different credit/debit card details to proceed.';
    }
  }

}
