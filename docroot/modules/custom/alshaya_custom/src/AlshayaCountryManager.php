<?php

namespace Drupal\alshaya_custom;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class AlshayaCountryManager.
 */
class AlshayaCountryManager {

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaCountryManager constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Function to return currency code for current country in requested language.
   *
   * @param string $country_code
   *   Country code.
   * @param string $lang_code
   *   Language code.
   *
   * @return string
   *   Currency code.
   */
  public function getCurrencyCode($country_code, $lang_code) {
    $country_code = strtolower($country_code);
    $lang_code = strtolower($lang_code);
    $currency = [];

    // KW.
    $currency['kw']['en'] = 'KWD';
    $currency['kw']['ar'] = 'د٠ك٠';

    // KSA.
    $currency['sa']['en'] = 'SAR';
    $currency['sa']['ar'] = '.ر.س';

    // UAE.
    $currency['ae']['en'] = 'AED';
    $currency['ae']['ar'] = 'د٠إ٠';

    // Egypt.
    $currency['eg']['en'] = 'EGP';
    $currency['eg']['ar'] = 'ج.م';

    // Invoke the alter hook to allow all modules to update the currency code.
    $this->moduleHandler->alter('alshaya_get_currency_code', $currency);

    return $currency[$country_code][$lang_code];
  }

}
