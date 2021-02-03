<?php

namespace Drupal\alshaya_bnpl\Helper;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
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
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * AlshayaBnplWidgetHelper Constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch,
                              LanguageManager $languageManager,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $skuManager) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->languageManager = $languageManager;
    $this->configFactory = $config_factory;
    $this->skuManager = $skuManager;
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
  public function getBnplWidgetInfo($page_type) {
    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $currency_power = (int) $currency_config->get('decimal_points');
    $info['data-currency'] = $currency_config->get('iso_currency_code');
    $info['data-num-instalments'] = 3;
    $info['data-locale'] = $this->languageManager->getCurrentLanguage()->getId();

    if ($page_type == 'pdp') {
      $info['data-type'] = 'product';
      $current_route = $this->currentRouteMatch->getParameters()->all();
      $node = $current_route['node'];
      if ($node->hasTranslation('en')) {
        $node = $node->getTranslation('en');
      }
      $product_sku = $this->skuManager->getSkuForNode($node);
      $sku_entity = SKU::loadFromSku($product_sku);

      if (empty($sku_entity)) {
        return [];
      }

      if ($sku_entity->hasTranslation('en')) {
        $sku_entity = $sku_entity->getTranslation('en');
      }
      $prices = $this->skuManager->getMinPrices($sku_entity);
      // Multiplying by the amount with the 10^Number-of-decimals to convert the
      // currency amount into smallest unit of currency.
      $info['data-amount'] = $prices['final_price'] * pow(10, $currency_power);
    }
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
    $bnpl_info['class'] = 'postpay-widget';
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => $bnpl_info,
    ];
  }

}
