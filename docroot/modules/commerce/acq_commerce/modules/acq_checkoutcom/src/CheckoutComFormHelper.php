<?php

namespace Drupal\acq_checkoutcom;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
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

    $form['cc_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name on card'),
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-name', 'checkoutcom-input'],
        'data-checkout' => 'card-name',
        'id' => 'cardName',
      ],
    ] + $states;

    $form['cc_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Credit Card Number'),
      '#default_value' => '',
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-number',
        'data-checkout' => 'card-number',
        'id' => 'cardNumber',
      ],
    ] + $states;

    $form['cc_exp_month'] = [
      '#type' => 'select',
      '#title' => $this->t('Expiration Month'),
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
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-month-select',
          'checkoutcom-input',
        ],
        'id' => 'cardMonth',
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
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-year-select',
          'checkoutcom-input',
        ],
        'id' => 'cardYear',
        'data-checkout' => 'expiry-year',
      ],
    ] + $states;

    $form['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => $this->t('Security code (CVV)'),
      '#default_value' => '',
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-cvv-input',
          'checkoutcom-input',
        ],
        'id' => 'cardCvv',
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
    if ($this->currentUser->isAuthenticated()) {
      $form['save_card'] = [
        '#type'  => 'checkbox',
        '#id' => 'saveCard',
        '#title' => $this->t('Save card for future use'),
      ];

      $form['cc_save_help_text'] = [
        '#type'  => 'markup',
        '#markup' => '<div class="cc-save-help-text">' . $this->t('This card will be securely saved for a faster payment experience. CVV number will not be saved. More Info') . '</div>',
      ];
    }

    $kit = $this->apiHelper->getCheckoutcomConfig('environment') == 'sandbox'
      ? 'acq_checkoutcom/sandbox_kit'
      : 'acq_checkoutcom/live_kit';

    $debug = $this->configFactory->get('acq_checkoutcom.settings')->get('debug') ? 'true' : 'false';
    $public_key = $this->apiHelper->getCheckoutcomConfig('public_key');
    $string = "window.CKOConfig = {
        debugMode: {$debug},
        publicKey: '{$public_key}',
      };";

    $form['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => $string,
      '#attached' => [
        'library' => [
          $kit,
          'acq_checkoutcom/checkoutcom.form',
        ],
      ],
    ];

    return $form;
  }

}
