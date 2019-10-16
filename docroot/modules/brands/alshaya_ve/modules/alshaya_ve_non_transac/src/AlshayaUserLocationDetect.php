<?php

namespace Drupal\alshaya_ve_non_transac;

use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AlshayaUserLocationDetect.
 */
class AlshayaUserLocationDetect {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaAcmConfigCheck constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Get Country code.
   *
   * Get user's browsed country if exists from store country
   * otherwise return all avaialable country.
   */
  public function getUserCountryCode() {
    if (!$this->getCountryCodeFromCookie()) {
      return $this->getAllStoresCountry();
    }
    $countryCode = $this->getCountryCodeFromCookie();
    if ($countryCode) {
      if (in_array($countryCode, $this->getAllStoresCountry())) {
        return [$countryCode];
      }
      else {
        return $this->getAllStoresCountry();
      }
    }
    return $this->getAllStoresCountry();
  }

  /**
   * Helper function to get latitute, longitute from cookies.
   */
  private function getCountryCodeFromCookie() {
    if ($country_code = $this->currentRequest->cookies->get('alshaya_client_country_code')) {
      return strtolower($country_code);
    }
    return FALSE;
  }

  /**
   * Helper function to get list of countries used in store form.
   *
   * @return array
   *   Country list array.
   */
  private function getAllStoresCountry() {
    $countryFieldValues = FieldStorageConfig::loadByName('node', 'field_store_country')->getSetting('allowed_values');
    unset($countryFieldValues['All']);
    return array_keys($countryFieldValues);
  }

}
