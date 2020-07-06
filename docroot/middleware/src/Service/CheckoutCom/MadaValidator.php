<?php

namespace App\Service\CheckoutCom;

use App\Service\Config\SystemSettings;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class MadaValidator.
 *
 * @package App\Service\CheckoutCom
 */
class MadaValidator {

  /**
   * Parameter Bag.
   *
   * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
   */
  protected $params;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * MadaValidator constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $params
   *   Parameter Bag.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(ParameterBagInterface $params, SystemSettings $settings) {
    $this->params = $params;
    $this->settings = $settings;
  }

  /**
   * Checks if the given bin is belong to mada bin.
   *
   * @param bool $is_live
   *   Flag to specify is application is in live mode or test.
   * @param string $bin
   *   The card bin to verify.
   *
   * @return bool
   *   Return true if one of the mada bin, false otherwise.
   */
  public function isMadaBin(bool $is_live, string $bin) {
    // @TODO: Future - replace this with Magento API call.
    // They have developed one for Mobile APP.
    // Remove the first row of csv columns.
    $madaBins = $is_live
      ? $this->settings->getSettings('mada_bins_live')
      : $this->settings->getSettings('mada_bins_test');

    return in_array($bin, $madaBins);
  }

}
