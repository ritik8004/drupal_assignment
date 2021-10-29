<?php

namespace Drupal\alshaya_payment_tabby;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Helper class for Tabby.
 *
 * @package Drupal\alshaya_payment_tabby
 */
class AlshayaTabbyHelper {

  use StringTranslationTrait;

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Config Factory.
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
   * Cache backend tabby.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Current Language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * AlshayaTabbyHelper Constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   Current route matcher service.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend checkout_com.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              CurrentRouteMatch $current_route_match,
                              LanguageManager $language_manager,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache) {
    $this->apiWrapper = $api_wrapper;
    $this->currentRouteMatch = $current_route_match;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->cache = $cache;
    $this->logger = $this->getLogger('AlshayaTabbyHelper');
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
  }

  /**
   * Get Tabby Widget Information.
   *
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   *
   * @return array|mixed
   *   Return array of keys.
   */
  private function getTabbyWidgetInfo($page_type = 'pdp') {
    $info['class'] = 'tabby-widget';
    switch ($page_type) {
      case 'cart':
        // Cart code.
        break;

      case 'checkout':
        // Checkout code.
        break;

      default:
        $info['id'] = 'tabby-promo-pdp';
        break;
    }
    return $info;
  }

  /**
   * Get Tabby Widget Markup.
   *
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   *
   * @return array|mixed
   *   Tabby renderable Widget.
   */
  public function getTabbyWidgetMarkup($page_type = 'pdp') {
    $tabby_info = $this->getTabbyWidgetInfo($page_type);
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => $tabby_info,
    ];
  }

  /**
   * Update build array required for Tabby by attaching library and settings.
   *
   * @param array $build
   *   Build.
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   */
  public function getTabbyPaymentBuild(array &$build, $page_type = 'pdp') {
    // No need to integrate the widget if tabby payment method is excluded
    // from the Checkout page.
    $config = $this->configFactory->get('alshaya_acm_checkout.settings');
    $excludedPaymemtMethods = $config->get('exclude_payment_methods');

    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config)
      ->applyTo($build);
    if (in_array('tabby', array_filter($excludedPaymemtMethods))) {
      return;
    }

    $tabbyApiConfig = $this->getTabbyApiConfig();

    // No need to integrate the widget if the API does not have merchant code.
    if (empty($tabbyApiConfig['merchant_code'])) {
      $this->logger->error('Merchant code is missing in Tabby config, @response', [
        '@response' => Json::encode($tabbyApiConfig),
      ]);
      return;
    }
    $tabbyApiConfig['locale'] = $this->langcode;
    $build['#attached']['drupalSettings']['tabby'] = $tabbyApiConfig;

    switch ($page_type) {
      case 'cart':
        // Cart code.
        break;

      case 'checkout':
        // Checkout code.
        break;

      default:
        $build['#attached']['library'][] = 'alshaya_payment_tabby/tabby_pdp';
        $build['tabby'] = $this->getTabbyWidgetMarkup();
        $widget_info = $this->getTabbyWidgetInfo();
        $build['#attached']['drupalSettings']['tabby']['selector'] = $widget_info['id'];
        $build['#attached']['drupalSettings']['tabby_widget_info'] = $widget_info;
        $build['#attached']['drupalSettings']['tabby']['tabby_installment_count'] = 4;
        break;
    }
  }

  /**
   * Get Tabby payment method config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getTabbyApiConfig($reset = FALSE) {

    static $configs;

    if (!empty($configs)) {
      return $configs;
    }

    $cache_key = 'alshaya_payment_tabby:api_configs';

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get('alshaya_payment_tabby_cache_time', 60);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;

    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $configs = $cache->data;
    }
    else {
      $response = $this->apiWrapper->invokeApi(
        'tabby/config',
        [],
        'GET'
      );

      $configs = Json::decode($response);

      if (empty($configs)) {
        $this->logger->error('Invalid response from Tabby api, @response', [
          '@response' => Json::encode($configs),
        ]);
      }
      elseif ($cache_time > 0) {
        // Cache only if enabled (cache_time set).
        $this->cache->set($cache_key, $configs, strtotime("+${cache_time} minutes"));
      }
    }

    // Try resetting once.
    if (empty($configs) && !($reset)) {
      return $this->getTabbyApiConfig(TRUE);
    }

    // @todo replace with $configs if mdc api works fine.
    return [
      'merchant_code' => 'uae_test',
      'public_key' => 'pk_test_99a77d42-a084-4fff-aee1-a8587483aa13',
    ];
  }

}
