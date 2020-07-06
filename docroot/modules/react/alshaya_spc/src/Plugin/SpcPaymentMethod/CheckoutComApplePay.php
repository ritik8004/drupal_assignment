<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checkout.com Apple Pay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_applepay",
 *   label = @Translation("Apple Pay"),
 * )
 */
class CheckoutComApplePay extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Checkout.com API Helper.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $checkoutComApiHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_checkoutcom.agent_api'),
      $container->get('config.factory')
    );
  }

  /**
   * CheckoutComApplePay constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_checkoutcom\ApiHelper $checkout_com_api_helper
   *   Checkout.com API Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ApiHelper $checkout_com_api_helper,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->checkoutComApiHelper = $checkout_com_api_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $status = $this->checkoutComApiHelper->getCheckoutcomConfig('applepay_enabled');
    if (!$status) {
      return FALSE;
    }

    $settings = $this->getApplePayConfig();
    if (empty($settings['merchantIdentifier'])) {
      $this->getLogger('checkout_com_applepay')->warning('Apple pay status enabled but no merchant identifier set, ignoring.');
      return FALSE;
    }

    if (!in_array($settings['allowedIn'], ['all', 'mobile'])) {
      $this->getLogger('checkout_com_applepay')->warning('Apple pay configuration for allowed in not valid, ignoring.');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $settings = $this->getApplePayConfig();
    $build['#attached']['drupalSettings']['checkoutComApplePay'] = $settings;
  }

  /**
   * Get all apple pay configuration.
   *
   * @return array
   *   Apple pay config.
   */
  protected function getApplePayConfig() {
    static $settings = NULL;

    if (isset($settings)) {
      return $settings;
    }

    // Data from API.
    $settings = [
      'merchantIdentifier' => $this->checkoutComApiHelper->getCheckoutcomConfig('applepay_merchant_id'),
      'supportedNetworks' => $this->checkoutComApiHelper->getCheckoutcomConfig('applepay_supported_networks'),
      // Adding supports3DS hardcoded here same as code in Magento plugin from
      // where we have copied the logic.
      'merchantCapabilities' => 'supports3DS,' . $this->checkoutComApiHelper->getCheckoutcomConfig('applepay_merchant_capabilities'),
      'supportedCountries' => $this->checkoutComApiHelper->getCheckoutcomConfig('applepay_supported_countries'),
    ];

    // Add site info from config.
    $settings += [
      'storeName' => $this->configFactory->get('system.site')->get('name'),
      'countryId' => $this->configFactory->get('system.date')->get('country.default'),
      'currencyCode' => $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code'),
    ];

    $settings['allowedIn'] = $this->checkoutComApiHelper->getApplePayAllowedIn();

    return $settings;
  }

}
