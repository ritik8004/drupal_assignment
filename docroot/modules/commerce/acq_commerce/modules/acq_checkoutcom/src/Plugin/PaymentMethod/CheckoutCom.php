<?php

namespace Drupal\acq_checkoutcom\Plugin\PaymentMethod;

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
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('checkout.com details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
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

//    $pane_form['payment_details']['cardType'] = [
//      '#type' => 'hidden',
//      '#value' => 'mada',
//      '#attributes' => [
//        'id' => 'cardType',
//      ],
//    ];

    $pane_form['payment_details']['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => "
        window.CKOConfig = {
          debugMode: true,
          // Replace with api call.
          publicKey: 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
          ready: function (event) {
            CheckoutKit.monitorForm('.multistep-checkout', CheckoutKit.CardFormModes.CARD_TOKENISATION);
          },
          cardTokenised: function(event) {
            cardToken.value = event.data.cardToken
            document.getElementById('multistep-checkout').submit();
          }
        };",
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // MDC will handle the part of payment just need to send card_token_id.
    $inputs = $form_state->getUserInput();
    $cart = $this->getCart();
    //$cart->setPaymentMethod('checkout_com', ['card_token_id' => $inputs['cko-card-token']]);
    $this->requestPayment($inputs);
    die();



  }

  protected function chargesToken($inputs) {
    $totals = $this->getCart()->totals();
    $url = "https://sandbox.checkout.com/api2/v2/charges/token";
    $ch = curl_init($url);
    $header = [
      'Content-Type: application/json;charset=UTF-8',
      'Authorization: sk_test_863d1545-5253-4387-b86b-df6a86797baa',
    ];

    $data_string = '{
      "value": "' . $totals['grand'] * 100 . '",
      "currency": "KWD",
      "cardToken": "' . $inputs['cko-card-token'] . '",
      "chargeMode": 2,
      "email": "testing@test.com",
	  }';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $output = curl_exec($ch);

    curl_close($ch);
    $decoded = json_decode($output, TRUE);
    echo '<pre>';
    print_r($decoded);
    echo '</pre>';
    die();
  }

  protected function requestPayment($inputs) {
    $totals = $this->getCart()->totals();
    $url = "https://api.sandbox.checkout.com/payments";
    $ch = curl_init($url);
    $header = [
      'Content-Type: application/json;charset=UTF-8',
      'Authorization: sk_test_863d1545-5253-4387-b86b-df6a86797baa',
    ];

    $data_string = '{
      source": {
        "type": "token",
        "token": "' . $inputs['cko-card-token'] . '",
      },
      "amount": "' . $totals['grand'] * 100 . '",
      "currency": "KWD",
      "3ds": {
        "enabled": true
      }
    }';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $output = curl_exec($ch);

    curl_close($ch);
    $decoded = json_decode($output, TRUE);
    echo '<pre>';
    print_r($decoded);
    echo '</pre>';
    die();


  }

}
