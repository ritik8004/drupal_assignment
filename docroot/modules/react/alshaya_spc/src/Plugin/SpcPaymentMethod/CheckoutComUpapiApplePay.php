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
 * Checkout.com UPAPI Apple Pay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_applepay",
 *   label = @Translation("ApplePay (Checkout.com)"),
 * )
 */
class CheckoutComUpapiApplePay extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

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
  public function processBuild(array &$build) {
    $settings = $this->getApplePayConfig();

    // API URL for checkout.com token.
    $api_url = $settings['api_url'] ?? 'https://api.sandbox.checkout.com';
    $api_url = trim($api_url, '/');
    $settings['api_url'] = $api_url . '/tokens';

    // Adding supports3DS to merchant capabilities
    // else merchant validation fails on apple-pay payment sheet.
    $settings['apple_pay_merchant_capabilities'] = 'supports3DS,' . $settings['apple_pay_merchant_capabilities'];

    $build['#attached']['drupalSettings']['checkoutComUpapiApplePay'] = $settings;
  }

  /**
   * Get checkout.com upapi apple pay configuration.
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
    $settings = $this->checkoutComApiHelper->getCheckoutcomUpapiApplePayConfig();

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
