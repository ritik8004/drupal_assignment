<?php

namespace Drupal\alshaya_tabby;

use Drupal\alshaya_acm_checkout\AlshayaBnplApiHelper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Helper class for Tabby.
 *
 * @package Drupal\alshaya_tabby
 */
class AlshayaTabbyWidgetHelper {

  use StringTranslationTrait;

  /**
   * BNPL Api Helper.
   *
   * @var \Drupal\alshaya_acm_checkout\AlshayaBnplApiHelper
   */
  protected $bnplApiHelper;

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
   * @param \Drupal\alshaya_acm_checkout\AlshayaBnplApiHelper $bnpl_api_helper
   *   Api wrapper.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(AlshayaBnplApiHelper $bnpl_api_helper,
                              LanguageManager $language_manager,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->bnplApiHelper = $bnpl_api_helper;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('AlshayaTabbyHelper');
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
    $excludedPaymentMethods = $config->get('exclude_payment_methods');

    if (in_array('tabby', array_filter($excludedPaymentMethods))) {
      return;
    }

    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config)
      ->applyTo($build);

    $tabbyApiConfig = $this->bnplApiHelper->getBnplApiConfig('tabby', 'tabby/config');

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
        $build['#attached']['library'][] = 'alshaya_tabby/tabby_pdp';
        $build['#attached']['library'][] = 'alshaya_white_label/tabby';
        $build['tabby'] = $this->getTabbyWidgetMarkup();
        $widget_info = $this->getTabbyWidgetInfo();
        $build['#attached']['drupalSettings']['tabby']['selector'] = $widget_info['id'];
        $build['#attached']['drupalSettings']['tabby_widget_info'] = $widget_info;
        $build['#attached']['drupalSettings']['tabby']['tabby_installment_count'] = 4;
        break;
    }
  }

}
