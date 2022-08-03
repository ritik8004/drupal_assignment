<?php

namespace Drupal\alshaya_bnpl\Helper;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Widget helper for Postpay.
 *
 * @package Drupal\alshaya_bnpl\Helper
 */
class AlshayaBnplWidgetHelper {

  use StringTranslationTrait;

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
   * The current route matcher service.
   *
   * @var \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper
   */
  protected $alshayaBnplAPIHelper;

  /**
   * AlshayaBnplWidgetHelper Constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper $alshayaBnplAPIHelper
   *   Alshaya BNPL Helper.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch,
                              LanguageManager $languageManager,
                              ConfigFactoryInterface $config_factory,
                              AlshayaBnplAPIHelper $alshayaBnplAPIHelper) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->languageManager = $languageManager;
    $this->configFactory = $config_factory;
    $this->alshayaBnplAPIHelper = $alshayaBnplAPIHelper;
  }

  /**
   * Get Postpay Widget Information.
   *
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   *
   * @return array|mixed
   *   Return array of keys.
   */
  private function getBnplWidgetInfo($page_type = 'pdp') {
    $info = [];
    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $country_code = $this->configFactory->get('system.date')->get('country.default');
    $info['class'] = 'postpay-widget';
    switch ($page_type) {
      case 'cart':
        $info['data-type'] = 'cart';
        break;

      case 'checkout':
        $info['data-type'] = 'payment-summary';
        $info['data-country'] = $country_code;
        break;

      default:
        $info['data-type'] = 'product';
        break;
    }
    $info['data-currency'] = $currency_config->get('iso_currency_code');
    $info['data-num-instalments'] = 3;
    $info['data-locale'] = $this->languageManager->getCurrentLanguage()->getId();
    return $info;
  }

  /**
   * Get Postpay Widget Markup.
   *
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   *
   * @return array|mixed
   *   BNPL renderable Widget.
   */
  public function getBnplWidgetMarkup($page_type = 'pdp') {
    $bnpl_info = $this->getBnplWidgetInfo($page_type);
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => $bnpl_info,
    ];
  }

  /**
   * Update build array required for Postpay by attaching library and settings.
   *
   * @param array $build
   *   Build.
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   */
  public function getBnplBuild(array &$build, $page_type = 'pdp') {
    // No need to integrate the widget if postpay payment method is excluded
    // from the Checkout page.
    $config = $this->configFactory->get('alshaya_acm_checkout.settings');
    $excludedPaymemtMethods = $config->get('exclude_payment_methods');
    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config)
      ->applyTo($build);
    if (in_array('postpay', array_filter($excludedPaymemtMethods))) {
      return;
    }

    $bnplApiconfig = $this->alshayaBnplAPIHelper->getBnplApiConfig();
    // No need to integrate the widget if the API does not have merchant id.
    if (!isset($bnplApiconfig['merchant_id']) || empty($bnplApiconfig['merchant_id'])) {
      return;
    }
    $bnplApiconfig['locale'] = 'en';
    $build['#attached']['drupalSettings']['postpay'] = $bnplApiconfig;

    // This is done to facilitate A/B testing.
    $postpay_mode = $this->configFactory->get('alshaya_bnpl.postpay')->get('postpay_mode');

    switch ($page_type) {
      case 'cart':
        $build['#attached']['library'][] = 'alshaya_bnpl/postpay_cart';
        $build['#attached']['library'][] = 'alshaya_white_label/postpay-cart';
        $build['#attached']['drupalSettings']['postpay_widget_info'] = $this->getBnplWidgetInfo('cart');
        $build['#attached']['drupalSettings']['alshaya_spc']['postpay_eligibility_message'] = $this->t('<p>Your order total does not qualify for payment via <span class="brand-postpay light">postpay</span>. <a href="#">Find out more</a> about our interest-free instalments and options with <span class="brand-postpay dark">postpay</span></p>');
        break;

      case 'checkout':
        $build['#attached']['library'][] = 'alshaya_white_label/postpay-checkout';
        $build['#attached']['drupalSettings']['postpay_widget_info'] = $this->getBnplWidgetInfo('checkout');
        break;

      default:
        $build['#attached']['library'][] = 'alshaya_bnpl/postpay_pdp';
        $build['postpay'] = $this->getBnplWidgetMarkup();

        // This is done to facilitate A/B testing.
        $build['postpay_mode_class']['#markup'] = '';
        if ($postpay_mode == 'hidden') {
          $build['postpay_mode_class']['#markup'] = 'postpay-hidden';
        }
        $build['#attached']['drupalSettings']['postpay_widget_info'] = $this->getBnplWidgetInfo();
        break;
    }
    $build['#attached']['library'][] = 'alshaya_bnpl/postpay_sdk';

    // This is done to facilitate A/B testing.
    $build['#attached']['drupalSettings']['postpay_widget_info']['postpay_mode_class'] = '';
    if ($postpay_mode == 'hidden') {
      $build['#attached']['library'][] = 'alshaya_bnpl/postpay_mode';
      $build['#attached']['drupalSettings']['postpay_widget_info']['postpay_mode_class'] = 'postpay-hidden';
    }

    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $build['#attached']['drupalSettings']['postpay']['currency_multiplier'] = 10 ** ((int) $currency_config->get('decimal_points'));
  }

}
