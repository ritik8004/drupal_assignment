<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\alshaya_api\AlshayaApiWrapper;

/**
 * Class AlshayaApiWrapperXbDecorator decorates AlshayaApiWrapper.
 */
class AlshayaApiWrapperXbDecorator extends AlshayaApiWrapper {

  /**
   * Domain config overrides.
   *
   * @var \Drupal\alshaya_xb\Service\DomainConfigOverrides
   */
  protected $domainConfig;

  /**
   * Set domain config overrides service.
   *
   * @param \Drupal\alshaya_xb\Service\DomainConfigOverrides $domain_config
   *   Domain config overrides.
   */
  public function setDomainConfigOverrides(DomainConfigOverrides $domain_config) {
    $this->domainConfig = $domain_config;
  }

  /**
   * {@inheritDoc}
   */
  public function getCustomerAddressForm() {
    // Get domain overrides.
    $configOverrides = $this->domainConfig->getConfigByDomain();

    // Get country code from domain overrides, if not available then
    // use site level country code.
    $country_code = $configOverrides['code'] ?? _alshaya_custom_get_site_level_country_code();

    return $this->getCustomerAddressFormByCountryCode(strtoupper($country_code));
  }

}
