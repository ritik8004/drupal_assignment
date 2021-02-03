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
   * List of routes where bazaarvoice script to be loaded.
   *
   * @var array
   */
  const BAZAAR_VOICE_ROUTES = [
    'entity.taxonomy_term.canonical:acq_product_category',
    'entity.node.canonical:acq_product',
    'acq_cart.cart',
    'alshaya_spc.checkout',
    'alshaya_spc.checkout.confirmation',
  ];

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
    $bv_config_check = $this->isBvConfigurationsAvailable();
    if ($bv_config_check) {
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
    $bvpixel_base_url = $this->configFactory->get('bazaar_voice.settings')->get('bvpixel_base_url');
    return $bvpixel_base_url ?? '';
  }

  /**
   * Get BV Client Name.
   *
   * @return string|null
   *   BV Client Name or empty.
   */
  public function getClientName() {
    $client_name = $this->configFactory->get('bazaar_voice.settings')->get('client_name');
    return $client_name ?? '';
  }

  /**
   * Get BV Site Id.
   *
   * @return string|null
   *   Site Id or empty.
   */
  public function getSiteId() {
    $siteId = $this->configFactory->get('bazaar_voice.settings')->get('site_id');
    return $siteId ?? '';
  }

  /**
   * Get BV environment.
   *
   * @return string|null
   *   Environment or empty.
   */
  public function getEnvironment() {
    $environment = $this->configFactory->get('bazaar_voice.settings')->get('environment');
    return $environment ?? '';
  }

  /**
   * Get BV locale.
   *
   * @return string|null
   *   Locale or empty.
   */
  public function getLocale() {
    $locale = $this->configFactory->get('bazaar_voice.settings')->get('locale');
    return $locale ?? '';
  }

  /**
   * Check if current route exists in defined list.
   *
   * @return bool
   *   True or false.
   */
  public function isCurrentRouteInBvList() {
    // Get current route identifier.
    $current_route_identifier = $this->getCurrentRouteIdentifier();
    // Check if route exists in the list defined.
    if (in_array($current_route_identifier, self::BAZAAR_VOICE_ROUTES)) {
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
            $routeIdentifier .= ':' . $term->getVocabularyId();
          }
          break;
      }
    }
    return $routeIdentifier ?? '';
  }

}
