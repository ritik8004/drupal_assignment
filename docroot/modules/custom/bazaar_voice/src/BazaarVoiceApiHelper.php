<?php

namespace Drupal\bazaar_voice;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Class BazaarVoice Api Helper.
 *
 * @package Drupal\bazaar_voice
 */
class BazaarVoiceApiHelper {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current route matcher service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * BazaarVoiceApiHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CurrentRouteMatch $currentRouteMatch) {
    $this->configFactory = $config_factory;
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * Get Bazaar voice BV pixel script url.
   *
   * @return string|string[]
   *   BV pixel script url.
   */
  public function getBvDynamicScriptCode() {
    $bv_config_available = $this->isBvConfigurationsAvailable();
    if ($bv_config_available) {
      $bazaar_voice_script_code = $this->getBvPixelBaseUrl() . '/' . $this->getClientName() . '/' . $this->getSiteId() . '/' . $this->getEnvironment() . '/' . $this->getLocale() . '/' . 'bv.js';
      return $bazaar_voice_script_code;
    }
    return '';
  }

  /**
   * Check if all BV configurations are set.
   *
   * @return bool
   *   True or false.
   */
  public function isBvConfigurationsAvailable() {
    $clientName = $this->getClientName();
    $siteId = $this->getSiteId();
    $environment = $this->getEnvironment();
    $locale = $this->getLocale();
    $bvpixel_base_url = $this->getBvPixelBaseUrl();
    if ($bvpixel_base_url && $clientName && $siteId && $environment && $locale) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get BV Pixel Base Url.
   *
   * @return string|null
   *   BV pixel base url or empty.
   */
  public function getBvPixelBaseUrl() {
    return $this->configFactory->get('bazaar_voice.settings')->get('bvpixel_base_url') ?? '';
  }

  /**
   * Get BV Client Name.
   *
   * @return string|null
   *   BV Client Name or empty.
   */
  public function getClientName() {
    return $this->configFactory->get('bazaar_voice.settings')->get('client_name') ?? '';
  }

  /**
   * Get BV Site Id.
   *
   * @return string|null
   *   Site Id or empty.
   */
  public function getSiteId() {
    return $this->configFactory->get('bazaar_voice.settings')->get('site_id') ?? '';
  }

  /**
   * Get BV environment.
   *
   * @return string|null
   *   Environment or empty.
   */
  public function getEnvironment() {
    return $this->configFactory->get('bazaar_voice.settings')->get('environment') ?? '';
  }

  /**
   * Get BV locale.
   *
   * @return string|null
   *   Locale or empty.
   */
  public function getLocale() {
    return $this->configFactory->get('bazaar_voice.settings')->get('locale') ?? '';
  }

  /**
   * Check if current route exists in defined list.
   *
   * @return bool
   *   True or false.
   */
  public function isCurrentRouteInBvList() {
    $bazaarvoice_routes_array = &drupal_static(__FUNCTION__, NULL);

    if (!isset($bazaarvoice_routes_array)) {
      // Get list of routes where we add BazaarVoice script will be loaded.
      $bazaarvoice_routes_config = $this->configFactory->get('bazaar_voice.settings')->get('bv_routes_list');
      $bazaarvoice_routes_array = array_map('trim', explode(PHP_EOL, $bazaarvoice_routes_config));
    }

    // Get current route identifier.
    $current_route_identifier = $this->getCurrentRouteIdentifier();

    // Check if current route exists in the route list.
    if (in_array($current_route_identifier, $bazaarvoice_routes_array)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get current route identifier.
   *
   * @return string|null
   *   Route identifier or empty.
   */
  public function getCurrentRouteIdentifier() {
    $routeIdentifier = $this->currentRouteMatch->getRouteName();
    $route_params = $this->currentRouteMatch->getParameters()->all();

    if (isset($routeIdentifier)) {
      switch ($routeIdentifier) {
        case 'entity.node.canonical':
          if (!empty($route_params) && isset($route_params['node'])) {
            /** @var \Drupal\node\Entity\Node $node */
            $node = $route_params['node'];
            $routeIdentifier .= ':' . $node->bundle();
          }
          break;

        case 'entity.taxonomy_term.canonical':
          if (!empty($route_params) && isset($route_params['taxonomy_term'])) {
            /** @var \Drupal\taxonomy\Entity\Term $term */
            $term = $route_params['taxonomy_term'];
            $routeIdentifier .= ':' . $term->bundle();
          }
          break;
      }
    }
    return $routeIdentifier ?? '';
  }

}
