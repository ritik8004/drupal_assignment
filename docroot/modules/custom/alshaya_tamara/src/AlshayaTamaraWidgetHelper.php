<?php

namespace Drupal\alshaya_tamara;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Helper class for Tamara.
 *
 * @package Drupal\alshaya_tamara
 */
class AlshayaTamaraWidgetHelper {
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
   * AlshayaTamaraHelper Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('AlshayaTamaraHelper');
  }

  /**
   * Get Tamara Widget Information.
   *
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   *
   * @return array|mixed
   *   Return array of keys.
   */
  private function getTamaraWidgetInfo($page_type = 'pdp') {
    $info = [];
    $info['class'] = 'tamara-installment-plan-widget';
    $id = 'tamara-card-checkout';
    $info['id'] = Html::getUniqueId($id);
    return $info;
  }

  /**
   * Get Tamara Widget Markup.
   *
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   *
   * @return array|mixed
   *   Tamara renderable Widget.
   */
  public function getTamaraWidgetMarkup($page_type = 'pdp') {
    $tamara_info = $this->getTamaraWidgetInfo($page_type);
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => $tamara_info,
    ];
  }

  /**
   * Update build array required for Tamara by attaching library and settings.
   *
   * @param array $build
   *   Build.
   * @param bool $page_type
   *   Type of the page ('pdp', 'cart', 'checkout').
   */
  public function getTamaraPaymentBuild(array &$build, $page_type = 'checkout') {
    // No need to integrate the widget if tamara payment method is excluded
    // from the Checkout page.
    $config = $this->configFactory->get('alshaya_acm_checkout.settings');
    $excludedPaymentMethods = $config->get('exclude_payment_methods');

    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config)
      ->applyTo($build);

    if (in_array('tamara', array_filter($excludedPaymentMethods))) {
      return;
    }

    // @todo need to confirm if this value will come from MDC or from config.
    $build['#attached']['drupalSettings']['tamara']['installmentCount'] = 3;

    $build['#attached']['library'][] = 'alshaya_tamara/tamara_checkout';
    $build['#attached']['library'][] = 'alshaya_white_label/tamara';
    $build['tamara'] = $this->getTamaraWidgetMarkup();
    $widget_info = $this->getTamaraWidgetInfo();

    $build['#attached']['drupalSettings']['tamara']['widgetInfo'] = $widget_info;
  }

}
