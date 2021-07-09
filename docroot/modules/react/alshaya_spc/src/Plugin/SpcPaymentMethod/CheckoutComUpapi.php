<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Checkout.com UPAPI payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi",
 *   label = @Translation("Credit / Debit Card"),
 * )
 */
class CheckoutComUpapi extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * API Wrapper.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $apiWrapper;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
      $container->get('alshaya_acm_checkoutcom.api_helper'),
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  /**
   * CheckoutCom constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper $api_wrapper
   *   API Wrapper.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaAcmCheckoutComAPIHelper $api_wrapper,
                              AccountInterface $current_user,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->apiWrapper = $api_wrapper;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $config = $this->apiWrapper->getCheckoutcomUpApiConfig();

    if (empty($config) || empty($config['public_key'])) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $config = $this->apiWrapper->getCheckoutcomUpApiConfig();

    $build['#cache']['contexts'] = ['user'];
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], ['user:' . $this->currentUser->id()]);

    $api_url = $config['api_url'] ?? 'https://api.sandbox.checkout.com';
    $api_url = trim($api_url, '/');

    $allowed_cards = explode(',', strtolower($config['allowed_card_types']));
    $allowed_cards_mapped = [];
    $allowed_cards_mapping = Settings::get('checkout_com_upapi_accepted_cards_mapping', []);
    foreach ($allowed_cards as $allowed_card) {
      $allowed_cards_mapped[$allowed_card] = $allowed_cards_mapping[$allowed_card] ?? '';
    }

    $tokenize = FALSE;
    $tokenizedCards = [];
    if ($config['vault_enabled']) {
      $tokenize = TRUE;
      $tokenizedCards = $this->apiWrapper->getSavedCards();
    }

    $build['#attached']['drupalSettings']['checkoutComUpapi'] = [
      'acceptedCards' => array_values(array_filter($allowed_cards_mapped)),
      'publicKey' => $config['public_key'],
      'apiUrl' => $api_url . '/tokens',
      'tokenizedCards' => $tokenizedCards,
      'tokenize' => $tokenize,
      'cvvCheck' => $config['cvv_check'],
      'processMada' => in_array('mada', $allowed_cards),
    ];

    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');

    // Add bin validation config in drupalSettings if it is enabled.
    $bin_validation_enabled = $checkout_settings->get('card_bin_validation_enabled') ?? FALSE;
    $bin_validation_supported_payment_methods = $checkout_settings->get('bin_validation_supported_payment_methods') ?? '';
    $card_bin_numbers = $checkout_settings->get('card_bin_numbers') ?? [];

    if ($bin_validation_enabled == TRUE
      && !empty($bin_validation_supported_payment_methods)
      && !empty($card_bin_numbers)) {
      $build['#attached']['drupalSettings']['checkoutComUpapi']['binValidation'] = [
        'cardBinValidationEnabled' => $bin_validation_enabled,
        'binValidationSupportedPaymentMethods' => $bin_validation_supported_payment_methods,
        'cardBinNumbers' => $card_bin_numbers,
      ];

      // Add payment method specific error message in strings.
      foreach (explode(',', $bin_validation_supported_payment_methods) ?? [] as $payment_method) {
        $build['#strings']['card_bin_validation_error_message_' . $payment_method] = [
          'key' => 'card_bin_validation_error_message_' . $payment_method,
          'value' => $this->t('Your card details are valid for NAPS Debit Card. Please select NAPS Debit Card as a payment method or enter different credit/debit card details to proceed.', [], ['context' => $payment_method]),
        ];
      }
    }

    $build['#strings']['invalid_card'] = [
      'key' => 'invalid_card',
      'value' => $this->t('Invalid Debit / Credit Card number'),
    ];

    $build['#strings']['invalid_expiry'] = [
      'key' => 'invalid_expiry',
      'value' => $this->t('Incorrect credit card expiration date'),
    ];

    $build['#strings']['invalid_cvv'] = [
      'key' => 'invalid_cvv',
      'value' => $this->t('Invalid security code (CVV)'),
    ];

    $build['#strings']['checkout_com_upapi_error_info'] = [
      'key' => 'checkout_com_upapi_error_info',
      'value' => $this->t('Order ID: @order_id'),
    ];
  }

}
