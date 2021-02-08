<?php

namespace Drupal\acq_checkoutcom;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides required elements for checkout card form.
 *
 * @package Drupal\acq_checkoutcom
 */
class CheckoutComFormHelper {

  use StringTranslationTrait;
  /**
   * API Helper service object.
   *
   * @var \Drupal\acq_commerce\APIHelper
   */
  protected $helper;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * CheckoutComAPIWrapper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   ApiHelper object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $account_proxy,
    ApiHelper $api_helper
  ) {
    $this->configFactory = $config_factory;
    $this->currentUser = $account_proxy;
    $this->apiHelper = $api_helper;
  }

  /**
   * Returns the card related necessary elements.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   Returns form elements.
   */
  public function newCardInfoForm(array $form, FormStateInterface $form_state) {
    $states = [
      '#states' => [
        'required' => [
          ':input[name="cko_card_token"]' => ['value' => ''],
        ],
      ],
    ];

    $form['cc_type'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-type-input', 'checkoutcom-input'],
      ],
    ];

    $form['cc_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Credit card number'),
      '#default_value' => '',
      '#id' => 'cardNumber',
      '#maxlength' => 19,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-number',
        'data-checkout' => 'card-number',
      ],
    ] + $states;

    $form['cc_exp_month'] = [
      '#type' => 'select',
      '#title' => $this->t('Expiration Month'),
      '#id' => 'cardMonth',
      '#options' => [
        '01' => '01',
        '02' => '02',
        '03' => '03',
        '04' => '04',
        '05' => '05',
        '06' => '06',
        '07' => '07',
        '08' => '08',
        '09' => '09',
        '10' => '10',
        '11' => '11',
        '12' => '12',
      ],
      '#default_value' => date('m'),
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-month-select',
          'checkoutcom-input',
        ],
      ],
    ] + $states;

    $year_options = [];
    $years_out = 10;
    for ($i = 0; $i <= $years_out; $i++) {
      $year = date('Y', strtotime("+{$i} year"));
      $year_options[$year] = $year;
    }

    $form['cc_exp_year'] = [
      '#type' => 'select',
      '#title' => $this->t('Expiration Year'),
      '#options' => $year_options,
      '#default_value' => date('Y'),
      '#id' => 'cardYear',
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-year-select',
          'checkoutcom-input',
        ],
        'data-checkout' => 'expiry-year',
      ],
    ] + $states;

    $form['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => $this->t('Security code (CVV)'),
      '#default_value' => '',
      '#id' => 'cardCvv',
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-cvv-input',
          'checkoutcom-input',
        ],
        'autocomplete' => 'cc-csc',
        'data-checkout' => 'cvv',
      ],
    ] + $states;

    $form['card_bin'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardBin',
      ],
    ];

    $form['cko_card_token'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardToken',
      ],
    ];

    // Card can be saved in account for authenticated users only.
    if ($this->currentUser->isAuthenticated() && $this->apiHelper->getCheckoutcomConfig('vault_enabled')) {
      $form['save_card'] = [
        '#type'  => 'checkbox',
        '#id' => 'saveCard',
        '#title' => $this->t('Save card for future use'),
      ];

      $form['cc_save_help_text'] = [
        '#type'  => 'markup',
        '#markup' => '<div class="cc-save-help-text">' . $this->t('This card will be securely saved for a faster payment experience. CVV number will not be saved.') . '</div>',
      ];
    }

    $form['#attached']['drupalSettings']['checkoutCom'] = [
      'debug' => $this->configFactory->get('acq_checkoutcom.settings')->get('debug') ? 'true' : 'false',
      'public_key' => $this->apiHelper->getCheckoutcomConfig('public_key'),
    ];

    return $form;
  }

  /**
   * Get all apple pay configuration.
   *
   * @param mixed $type
   *   Apple-pay or upapi.
   *
   * @return array
   *   Apple pay config.
   */
  public function getApplePayConfig($type = NULL) {
    // Data from API.
    if ($type == 'upapi') {
      $upapiConfig = $this->apiHelper->getCheckoutcomUpapiApplePayConfig();
      $settings = [
        'merchantIdentifier' => $upapiConfig['apple_pay_merchant_id'],
      ];
    }
    else {
      $settings = [
        'merchantIdentifier' => $this->apiHelper->getCheckoutcomConfig('applepay_merchant_id'),
        'supportedNetworks' => $this->apiHelper->getCheckoutcomConfig('applepay_supported_networks'),
        // Adding supports3DS hardcoded here same as code in Magento plugin from
        // where we have copied the logic.
        'merchantCapabilities' => 'supports3DS,' . $this->apiHelper->getCheckoutcomConfig('applepay_merchant_capabilities'),
        'supportedCountries' => $this->apiHelper->getCheckoutcomConfig('applepay_supported_countries'),
      ];
    }

    // Add site info from config.
    $settings += [
      'storeName' => $this->configFactory->get('system.site')->get('name'),
      'countryId' => $this->configFactory->get('system.date')->get('country.default'),
      'currencyCode' => $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code'),
    ];

    return $settings;
  }

  /**
   * Get all apple pay secret info.
   *
   * @return array
   *   Apple pay config.
   */
  public function getApplePaySecretInfo() {
    // Add secret info from $settings.
    $secret_info = Settings::get('apple_pay_secret_info');
    $settings = [
      'merchantCertificateKey' => $secret_info['merchantCertificateKey'],
      'merchantCertificatePem' => $secret_info['merchantCertificatePem'],
      'merchantCertificatePass' => $secret_info['merchantCertificatePass'],
    ];

    return $settings;
  }

}
