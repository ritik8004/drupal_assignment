<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the CheckoutCom mada card payment method.
 *
 * @ACQPaymentMethod(
 *   id = "checkout_com_mada",
 *   label = @Translation("Checkout.com Mada"),
 * )
 */
class CheckoutComMada extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * Checkout.com api wrapper object.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * CheckoutCom constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_cart\CartInterface $cart
   *   The shopping cart.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartInterface $cart) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $cart);
    $this->checkoutComApi = \Drupal::service('acq_checkoutcom.checkout_api');
    $this->configFactory = \Drupal::service('config.factory');
    $this->currentUser = \Drupal::service('current_user');
    $this->apiHelper = \Drupal::service('acq_checkoutcom.agent_api');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('checkout.com mada details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $states = [
      '#states' => [
        'required' => [
          ':input[name="acm_payment_methods[payment_details_wrapper][payment_method_checkout_com_mada][payment_details][card_token]"]' => ['value' => ''],
        ],
      ],
    ];

    $pane_form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
    ];

    $pane_form['payment_details']['cc_number'] = [
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

    $pane_form['payment_details']['cc_cvv'] = [
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

    $pane_form['payment_details']['cc_exp_month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Month'),
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-month-select',
          'checkoutcom-input',
        ],
        'id' => 'expMonth',
        'data-checkout' => 'expiry-month',
      ],
    ] + $states;

    $pane_form['payment_details']['cc_exp_year'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Year'),
      '#attributes' => [
        'class' => [
          'checkoutcom-credit-card-exp-year-select',
          'checkoutcom-input',
        ],
        'id' => 'expYear',
        'data-checkout' => 'expiry-year',
      ],
    ] + $states;

    $pane_form['payment_details']['card_token'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardToken',
      ],
    ];

    $pane_form['payment_details']['card_bin'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardBin',
      ],
    ];

    $pane_form['payment_details']['cardType'] = [
      '#type' => 'hidden',
      '#value' => 'mada',
      '#attributes' => [
        'id' => 'cardType',
      ],
    ];

    $debug = $this->configFactory->get('acq_checkoutcom.settings')->get('debug') ? 'true' : 'false';
    $string = "window.CKOConfig = {
      debugMode: {$debug},
      publicKey: '{$this->apiHelper->getSubscriptionKeys('public_key')}',
      ready: function (event) {
        CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
      },
      cardTokenised: function(event) {
        cardBin.value = event.data.card.bin;
        cardToken.value = event.data.cardToken;
        cardNumber.value = ''
        cardCvv.value = ''
        expMonth.value = ''
        expYear.value = ''
        document.getElementById('multistep-checkout').submit();
      }
    };";

    $pane_form['payment_details']['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => $string,
      '#attached' => [
        'library' => ['acq_checkoutcom/checkoutcom.kit'],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    parent::validatePaymentForm($pane_form, $form_state, $complete_form);
    $card_bin = $form_state->getValue('acm_payment_methods')['payment_details_wrapper']['payment_method_checkout_com_mada']['payment_details']['card_bin'];
    if (empty($card_bin) || ($this->checkoutComApi->isMadaEnabled() && !$this->checkoutComApi->isMadaBin($card_bin))) {
      $form_state->setError(
        $pane_form['payment_details_wrapper']['payment_method_checkout_com_mada']['payment_details']['cc_number'],
        $this->t('Entered Card is not mada card.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // MDC will handle the part of payment just need to send card_token_id.
    $inputs = $form_state->getUserInput();
    $card_bin = $form_state->getValue('acm_payment_methods')['payment_details_wrapper']['payment_method_checkout_com_mada']['payment_details']['card_bin'];
    if (!empty($inputs['cko-card-token']) && !empty($card_bin)) {
      $this->initiateMadaCardPayment($inputs, $card_bin);
    }
  }

  /**
   * Process 3d secure payment for new card.
   *
   * @param array $inputs
   *   The array of inputs from user.
   * @param string $card_bin
   *   The card bin.
   *
   * @throws \Exception
   */
  protected function initiateMadaCardPayment(array $inputs, $card_bin) {
    $cart = $this->getCart();
    $totals = $cart->totals();

    // Process 3d secure payment.
    $this->checkoutComApi->processCardPayment(
      $cart,
      [
        'value' => $totals['grand'] * CheckoutComAPIWrapper::MULTIPLY_HUNDREDS,
        'cardToken' => $inputs['cko-card-token'],
        'email' => $cart->customerEmail(),
        'udf1' => $this->checkoutComApi->isMadaBin($card_bin) ? 'MADA' : '',
      ],
      TRUE
    );
  }

}
