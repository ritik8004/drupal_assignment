<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\RequestStack;

/**
 * Alshaya XB domain mapping service.
 *
 * @package Drupal\alshaya_xb\Service
 */
class AlshayaXbDomainMapping {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Request Stack.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $requestStack;

  /**
   * AlshayXbDomainMapping constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory.
   * @param \Drupal\Core\Http\RequestStack $request_stack
   *   Request Stack.
   */
  public function __construct(
    ConfigFactory $config_factory,
    RequestStack $request_stack
  ) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
  }

  /**
   * Get XB config data by domain or site code.
   *
   * @return array|null
   *   XB data array by domain or site prefix.
   */
  public function getXbConfigByDomain(): ?array {
    $config = $this->configFactory->get('alshaya_xb.settings');
    $domainMappings = $config->get('domain_mapping');

    // Get current base url.
    $base_url = $this->requestStack->getCurrentRequest()->getHost();

    $xbConfig = NULL;

    foreach ($domainMappings as $domainMapping) {
      // Get domain and prefix comma separated.
      $domains = $domainMapping['domains'];
      $domain_prefix = explode(',', $domains);
      $domain = $domain_prefix[0];
      $prefix = $domain_prefix[1];
      if (strstr($base_url, $domain) || strstr($base_url, $prefix)) {
        // Check if base_url has domain or the site prefix.
        // then collect xb config data.
        $xbConfig = $domainMapping;
      }
    }

    return $xbConfig;
  }

}
