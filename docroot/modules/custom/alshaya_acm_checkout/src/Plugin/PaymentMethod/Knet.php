<?php

namespace Drupal\alshaya_acm_checkout\Plugin\PaymentMethod;

use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Braintree payment method.
 *
 * @ACQPaymentMethod(
 *   id = "knet",
 *   label = @Translation("K-Net Debit Card"),
 * )
 */
class Knet extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return 'K-Net details here.';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();
    $payment_method = $cart->getPaymentMethod();
    $payment_method_name = $payment_method['method'];
    $payment_data = isset($payment_method['additional_data']) ? $payment_method['additional_data'] : [];
    $nonce = isset($payment_data['payment_method_nonce']) ? $payment_data['payment_method_nonce'] : NULL;

    // If payment details have already been filled out, don't show the form.
    if ($payment_method_name == $this->getId() && isset($nonce)) {
      $pane_form['payload_nonce'] = [
        '#type' => 'hidden',
        '#default_value' => $nonce,
      ];
      $pane_form['complete_message'] = [
        '#markup' => $this->t('K-Net information already entered.'),
      ];
      return $pane_form;
    }

    $pane_form['payload_nonce'] = [
      '#type' => 'hidden',
      '#default_value' => '',
    ];

    $pane_form['#theme'] = ['braintree'];
    $pane_form['#attached'] = [
      'library' => ['alshaya_acm_checkout/knet'],
      'drupalSettings' => [
        'knet' => [
          'authorizationToken' => $this->getToken(),
        ],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $nonce = $values['payment_details']['payload_nonce'];
    if (empty($nonce)) {
      $form_state->setError($pane_form, $this->t('There was an issue with the debit card details.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $nonce = $values['payment_details']['payload_nonce'];
    $cart = $this->getCart();
    $cart->setPaymentMethodData([
      'payment_method_nonce' => $nonce,
      'cc_type' => '',
    ]);
  }

  /**
   * Get and cache the token used for a transaction.
   */
  public function getToken() {
    $cart = $this->getCart();
    $cid = 'knet_token:' . $cart->id();
    $token = NULL;

    if ($cache = \Drupal::cache()->get($cid)) {
      $token = $cache->data;
    }
    else {
      $token = \Drupal::service('acq_commerce.api')->getPaymentToken('braintree');
      \Drupal::cache()->set($cid, $token);
    }

    return $token;
  }

}
