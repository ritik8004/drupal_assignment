<?php

namespace Drupal\alshaya_tabby;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Cache\Cache;

/**
 * Helper class for Tabby.
 *
 * @package Drupal\alshaya_tabby
 */
class AlshayaTabbyWidgetHelper {

  use StringTranslationTrait;

  /**
   * Tabby Api Helper.
   *
   * @var \Drupal\alshaya_tabby\AlshayaTabbyApiHelper
   */
  protected $tabbyApiHelper;

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
   * @param \Drupal\alshaya_tabby\AlshayaTabbyApiHelper $tabby_api_helper
   *   Api wrapper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(AlshayaTabbyApiHelper $tabby_api_helper,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->tabbyApiHelper = $tabby_api_helper;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('AlshayaTabbyHelper');
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
    $info = [];
    $info['class'] = 'tabby-widget';
    $id = match ($page_type) {
      'cart' => 'tabby-promo-cart',
        'checkout' => 'tabby-card-checkout',
        default => 'tabby-promo-pdp',
    };
    $info['id'] = Html::getUniqueId($id);
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
    $tabby_config = $this->configFactory->get('alshaya_tabby.settings');

    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config)
      ->addCacheableDependency($tabby_config)
      ->applyTo($build);

    if (in_array('tabby', array_filter($excludedPaymentMethods))) {
      return;
    }

    $tabbyApiConfig = $this->tabbyApiHelper->getTabbyApiConfig();
    // No need to integrate the widget if the API does not have merchant code.
    if (empty($tabbyApiConfig['merchant_code'])) {
      $this->logger->error('Merchant code is missing in Tabby config, @response', [
        '@response' => Json::encode($tabbyApiConfig),
      ]);
      return;
    }
    $tabbyApiConfig['locale'] = $this->langcode;
    $build['#attached']['drupalSettings']['tabby'] = $tabbyApiConfig;
    $build['#attached']['drupalSettings']['tabby']['installmentCount'] = 4;
    $build['#attached']['drupalSettings']['tabby']['showTabbyWidget'] = $tabby_config->get('show_tabby_widget');

    switch ($page_type) {
      case 'cart':
        $widget_info = $this->getTabbyWidgetInfo('cart');
        $build['tabby'] = $this->getTabbyWidgetMarkup('cart');
        $build['#attached']['library'][] = 'alshaya_tabby/tabby_cart';
        $build['#attached']['library'][] = 'alshaya_white_label/tabby';
        $build['#attached']['drupalSettings']['tabby']['cart_widget_limit'] = $tabby_config->get('cart_widget_limit');
        $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], $tabby_config->getCacheTags());
        break;

      case 'checkout':
        $build['#attached']['library'][] = 'alshaya_tabby/tabby_card';
        $build['#attached']['library'][] = 'alshaya_white_label/tabby';
        $widget_info = $this->getTabbyWidgetInfo('checkout');
        break;

      default:
        $build['#attached']['library'][] = 'alshaya_tabby/tabby_pdp';
        $build['#attached']['library'][] = 'alshaya_white_label/tabby';
        $build['tabby'] = $this->getTabbyWidgetMarkup();
        $widget_info = $this->getTabbyWidgetInfo();
        break;
    }
    $build['#attached']['drupalSettings']['tabby']['widgetInfo'] = $widget_info;
  }

}
