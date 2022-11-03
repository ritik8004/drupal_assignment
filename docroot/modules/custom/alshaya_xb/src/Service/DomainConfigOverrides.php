<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\RequestStack;

/**
 * Provides config overrides based on domain.
 *
 * @package Drupal\alshaya_xb\Service
 */
class DomainConfigOverrides {

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
   *   XB data array by domain or null.
   */
  public function getXbConfigByDomain(): ?array {
    $config = $this->configFactory->get('alshaya_xb.settings');
    $domainMappings = $config->get('domain_mapping');

    // Get current base url.
    $base_url = $this->requestStack->getCurrentRequest()->getHost();

    $xbConfig = NULL;

    foreach ($domainMappings as $domainMapping) {
      // Get domain and prefix comma separated.
      $domains = explode(',', $domainMapping['domains']);
      foreach ($domains as $domain) {
        // Check if base_url has domain or the site prefix.
        if (strstr($base_url, $domain)) {
          $xbConfig = $domainMapping;
          break 2;
        }
      }
    }

    return $xbConfig;
  }

}
