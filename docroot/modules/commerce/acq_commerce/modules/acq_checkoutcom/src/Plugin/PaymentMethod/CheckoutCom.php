<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the CheckoutCom payment method.
 *
 * @ACQPaymentMethod(
 *   id = "checkout_com",
 *   label = @Translation("Checkout.com"),
 * )
 */
class CheckoutCom extends PaymentMethodBase implements PaymentMethodInterface {

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
    $this->checkoutComApi = \Drupal::service('acq_checkoutcom.api');
    $this->configFactory = \Drupal::service('config.factory');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('checkout.com details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $checkout_com_settings = $this->configFactory->get('acq_checkoutcom.settings');

    $pane_form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
      '#attached' => [
        'library' => ['acq_checkoutcom/checkoutcom.kit'],
      ],
    ];

    $pane_form['payment_details']['cc_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Credit Card Number'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-number',
        'data-checkout' => 'card-number',
      ],
    ];

    $pane_form['payment_details']['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => $this->t('Security code (CVV)'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-cvv-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-csc',
        'data-checkout' => 'cvv',
      ],
    ];

    $pane_form['payment_details']['cc_exp_month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Month'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-exp-month-select', 'checkoutcom-input'],
        'data-checkout' => 'expiry-month',
      ],
    ];

    $pane_form['payment_details']['cc_exp_year'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Year'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-exp-year-select', 'checkoutcom-input'],
        'data-checkout' => 'expiry-year',
      ],
    ];

    $pane_form['payment_details']['card_token'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardToken',
      ],
    ];

    $debug = $checkout_com_settings->get('debug') ? 'true' : 'false';
    // @todo: Replace with api call.
    $public_key = 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f';

    $string = "window.CKOConfig = {
      debugMode: {$debug},
      publicKey: '{$public_key}',
      ready: function (event) {
        CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
      },
      cardTokenised: function(event) {
        cardToken.value = event.data.cardToken
        document.getElementById('multistep-checkout').submit();
      },
      apiError: function (event) {
      }
    };";

    $pane_form['payment_details']['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => $string,
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // MDC will handle the part of payment just need to send card_token_id.
    $inputs = $form_state->getUserInput();
    // @todo: Replace this with APi call + cache / config.
    $process_type = '3d';
    if ($process_type == '2d') {
      $cart = $this->getCart();
      $cart->setPaymentMethod($this->getId(), ['card_token_id' => $inputs['cko-card-token']]);
    }
    elseif ($process_type == '3d') {
      if (!empty($inputs['cko-card-token'])) {
        $this->initiate3dSecurePayment($inputs);
      }
    }
  }

  /**
   * Process 3d secure payment for new card.
   *
   * @param array $inputs
   *   The array of inputs from user.
   *
   * @throws \Exception
   */
  protected function initiate3dSecurePayment(array $inputs) {
    $cart = $this->getCart();
    $totals = $cart->totals();
    // Process 3d secure payment.
    $this->checkoutComApi->processCardPayment(
      $cart,
      [
        'value' => $totals['grand'] * 100,
        'cardToken' => $inputs['cko-card-token'],
        'email' => $cart->customerEmail(),
      ],
      TRUE
    );
  }

}
