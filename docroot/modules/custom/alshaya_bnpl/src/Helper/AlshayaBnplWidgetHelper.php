<?php

namespace Drupal\alshaya_bnpl\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Widget helper for Postpay.
 *
 * @package Drupal\alshaya_bnpl\Helper
 */
class AlshayaBnplWidgetHelper {

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
  public function getBnplWidgetInfo($page_type = 'pdp') {
    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $info['class'] = 'postpay-widget';
    switch ($page_type) {
      case 'cart':
        $info['data-type'] = 'cart';
        break;

      case 'checkout':
        $info['data-type'] = 'payment-summary';
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
    $bnplApiconfig = $this->alshayaBnplAPIHelper->getBnplApiConfig();
    // No need to integrate the widget if the API does not have merchant id.
    if (!isset($bnplApiconfig['merchant_id']) || empty($bnplApiconfig['merchant_id'])) {
      return;
    }
    $bnplApiconfig['locale'] = 'en';
    switch ($page_type) {
      case 'cart':
        $build['#attached']['library'][] = 'alshaya_bnpl/postpay_sdk';
        break;

      default:
        $build['#attached']['library'][] = 'alshaya_bnpl/postpay_pdp';
        break;
    }
    $build['#attached']['drupalSettings']['postpay'] = $bnplApiconfig;

    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $build['#attached']['drupalSettings']['postpay']['currency_multiplier'] = pow(10, (int) $currency_config->get('decimal_points'));
  }

}
