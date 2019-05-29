<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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
    $config = $this->configFactory->get('acq_checkoutcom.settings');

    // @todo: Repalce this code with API call.
    $file = drupal_get_path('module', 'acq_checkoutcom') . '/saved_card.json';
    $data = file_get_contents($file);
    $existing_cards = !empty($data) ? json_decode($data) : [];

    $options = [];
    foreach ($existing_cards as $card) {
      $options[$card->id] = '**** **** **** ' . $card->last4;
    }

    $payment_type = \Drupal::requestStack()->getCurrentRequest()->query->get('type');

    $pane_form['payment_type'] = [
      '#type' => 'radios',
      '#options' => [
        'existing' => $this->t('Existing Card'),
        'new' => $this->t('New Card'),
      ],
      '#default_value' => !empty($options) && ($payment_type == 'existing' || empty($payment_type)) ? 'existing' : 'new',
      '#ajax' => [
        'url' => Url::fromRoute('acq_checkoutcom.select_card_type'),
        'wrapper' => 'payment_details_checkout_com',
        'effect' => 'fade',
      ],
      '#attached' => [
        'library' => ['acq_checkoutcom/checkoutcom.kit'],
      ],
    ];

    $pane_form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
    ];

    if (!empty($options) && ($payment_type == 'existing' || empty($payment_type))) {
      $pane_form['payment_details']['existing_card'] = [
        '#type' => 'radios',
        '#title' => $this->t('Existing cards'),
        '#title_display' => 'invisible',
        '#options' => $options,
      ];
    }
    elseif ($payment_type == 'new') {
      $pane_form['payment_details']['cc_number'] = [
        '#type' => 'tel',
        '#title' => $this->t('Credit Card Number'),
        '#default_value' => '',
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
        '#attributes' => [
          'class' => ['checkoutcom-credit-card-cvv-input', 'checkoutcom-input'],
          'autocomplete' => 'cc-csc',
          'data-checkout' => 'cvv',
        ],
      ];

      $pane_form['payment_details']['cc_exp_month'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Expiration Month'),
        '#attributes' => [
          'class' => ['checkoutcom-credit-card-exp-month-select', 'checkoutcom-input'],
          'data-checkout' => 'expiry-month',
        ],
      ];

      $pane_form['payment_details']['cc_exp_year'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Expiration Year'),
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

      $pane_form['payment_details']['checkout_kit'] = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => "
        window.CKOConfig = {
          debugMode: " . $config->get('debug') . ",
          // Replace with api call.
          publicKey: 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
          ready: function (event) {
            console.log('card is ready');
            CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
          },
          cardTokenised: function(event) {
            cardToken.value = event.data.cardToken
            document.getElementById('multistep-checkout').submit();
          },
          apiError: function (event) {
          },
        };",
      ];
    }

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
      else {
        $acm_payment_methods = $form_state->getValue('acm_payment_methods');
        $card_id = $acm_payment_methods['payment_details_wrapper']['payment_method_checkout_com']['payment_details']['existing_card'];
        $this->initiateStoredCardPayment($card_id);
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
        'email' => 'testing@test.com',
      ],
      TRUE
    );
  }

  /**
   * Process 3d secure payment for stored card.
   *
   * @param string $card_id
   *   The stored card unique id.
   *
   * @throws \Exception
   */
  protected function initiateStoredCardPayment(string $card_id) {
    $cart = $this->getCart();
    $totals = $cart->totals();

    $this->checkoutComApi->processCardPayment($cart, [
      'cardId' => $card_id,
      'value' => $totals['grand'] * 100,
      'email' => 'mitesh+cards@axelerant.com',
    ]);
  }

}
