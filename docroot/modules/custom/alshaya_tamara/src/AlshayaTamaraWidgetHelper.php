<?php

namespace Drupal\alshaya_tamara;

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
   * Tamara Api Helper.
   *
   * @var \Drupal\alshaya_tamara\AlshayaTamaraApiHelper
   */
  protected $tamaraApiHelper;

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
   * @param \Drupal\alshaya_tamara\AlshayaTamaraApiHelper $tamara_api_helper
   *   Api wrapper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(AlshayaTamaraApiHelper $tamara_api_helper,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->tamaraApiHelper = $tamara_api_helper;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('AlshayaTamaraHelper');
  }

  /**
   * Update build array required for Tamara by attaching library and settings.
   *
   * @param array $build
   *   Build.
   */
  public function getTamaraPaymentBuild(array &$build) {
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

    $tamaraApiConfig = $this->tamaraApiHelper->getTamaraApiConfig();
    // No need to integrate the widget if the reponse does not have is_active or
    // set to FALSE.
    if (!isset($tamaraApiConfig['is_active'])
      || !((bool) $tamaraApiConfig['is_active'])) {
      $this->logger->error('Tamara payment method is not avtive in Tamara config, @response', [
        '@response' => Json::encode($tamaraApiConfig),
      ]);
      return;
    }

    // Pass the tamara active status in the drupal settings.
    $build['#attached']['drupalSettings']['tamara']['status'] = TRUE;

    // Check if the public key is set in tamara api config and pass in drupal
    // settings for rendering tamara widget on checkout page.
    $build['#attached']['drupalSettings']['tamara']['publicKey'] = $tamaraApiConfig['public_key'] ?: '';

    // Get the installment count from the Alshaya Tamara module's config.
    $alshayaTamaraConfig = $this->configFactory->get('alshaya_tamara.settings');
    $build['#attached']['drupalSettings']['tamara']['installmentCount'] = $alshayaTamaraConfig->get('installmentCount');

    // Attach the libraries for tamara widgets.
    $build['#attached']['library'][] = 'alshaya_tamara/tamara_checkout';
    $build['#attached']['library'][] = 'alshaya_white_label/tamara';
  }

}
