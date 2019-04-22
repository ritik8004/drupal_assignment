<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

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
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('checkout.com details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
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
      '#default_value' => !empty($options) && ($payment_type == 'existing' || empty($payment_type))  ? 'existing' : 'new',
      '#ajax' => [
        'url' => Url::fromRoute('acq_checkoutcom.select_card_type'),
        // 'callback' => [get_class($this), 'renderRelevantFields'],
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
          debugMode: true,
          // Replace with api call.
          publicKey: 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
          ready: function (event) {
            console.log('card is ready');
            CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
          },
          cardTokenised: function(event) {
            console.log(event);
            cardToken.value = event.data.cardToken
            document.getElementById('multistep-checkout').submit();
          },
          apiError: function (event) {
            console.log(event);
          },
        };",
      ];
    }

    return $pane_form;
  }

  public function renderRelevantFields(&$form, FormStateInterface $form_state) {
    return $form['acm_payment_methods']['payment_details_wrapper']['payment_method_checkout_com']['payment_details'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // MDC will handle the part of payment just need to send card_token_id.
    $inputs = $form_state->getUserInput();

    // $cart = $this->getCart();
    // $cart->setPaymentMethod($this->getId(), ['card_token_id' => $inputs['cko-card-token']]);
    if (!empty($inputs['cko-card-token'])) {
      $this->chargesToken($inputs);
      die();
    }
    else {
      $card_id = $form_state->getValue('acm_payment_methods')['payment_details_wrapper']['payment_method_checkout_com']['payment_details']['existing_card'];
      $this->chargesCard($card_id);
      die();
    }
  }

  protected function chargesToken($inputs) {
    $totals = $this->getCart()->totals();

    $output = acq_checkoutcom_custom_curl_request(
      'https://sandbox.checkout.com/api2/v2/charges/token',
      [
        'value' => $totals['grand'] * 100,
        'currency' => 'KWD',
        'cardToken' => $inputs['cko-card-token'],
        'chargeMode' => 2,
        'email' => 'testing@test.com',
        'autoCapture' => 'Y',
        'successUrl' => Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString(),
        'failUrl' => Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString(),
      ]
    );

    echo '<pre>';
    print_r($output);
    echo '</pre>';
    die();
  }

  protected function chargesCard($card_id) {
    $totals = $this->getCart()->totals();

    $output = acq_checkoutcom_custom_curl_request(
      'https://sandbox.checkout.com/api2/v2/charges/card',
      [
        'cardId' => $card_id,
        'value' => $totals['grand'] * 100,
        'currency' => 'KWD',
        'chargeMode' => 2,
        'email' => 'mitesh+cards@axelerant.com',
        'autoCapture' => 'Y',
        'successUrl' => Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString(),
        'failUrl' => Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString(),
      ]
    );

    echo '<pre>';
    print_r($output);
    echo '</pre>';
    die();
  }

  protected function requestPayment($inputs) {
    $totals = $this->getCart()->totals();

    $output = acq_checkoutcom_custom_curl_request(
      'https://api.sandbox.checkout.com/payments',
      [
        'source' => [
          'type' => 'token',
          'token' => $inputs['cko-card-token'],
        ],
        'amount' => $totals['grand'] * 100,
        'currency' => 'USD',
        '3ds' => [
          'enabled' => TRUE,
        ]
      ]
    );

    echo '<pre>';
    print_r($output);
    echo '</pre>';
    die();
  }

}
