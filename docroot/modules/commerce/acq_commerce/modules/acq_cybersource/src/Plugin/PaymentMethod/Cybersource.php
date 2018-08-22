<?php

namespace Drupal\acq_cybersource\Plugin\PaymentMethod;

use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Cybersource payment method.
 *
 * @ACQPaymentMethod(
 *   id = "cybersource",
 *   label = @Translation("Cybersource"),
 * )
 */
class Cybersource extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('Cybersource details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details'],
      ],
      '#attached' => [
        'library' => ['acq_cybersource/cybersource'],
      ],
    ];

    $pane_form['payment_details']['cc_type'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => ['cybersource-credit-card-type-input', 'cybersource-input'],
      ],
    ];

    $pane_form['payment_details']['cc_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Credit Card Number'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['cybersource-credit-card-input', 'cybersource-input'],
        'autocomplete' => 'cc-number',
      ],
    ];

    $pane_form['payment_details']['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => $this->t('Security code (CVV)'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['cybersource-credit-card-cvv-input', 'cybersource-input'],
        'autocomplete' => 'cc-csc',
      ],
    ];

    $pane_form['payment_details']['cc_exp_month'] = [
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
      '#default_value' => date('m'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['cybersource-credit-card-exp-month-select', 'cybersource-input'],
      ],
    ];

    $year_options = [];
    $years_out = 10;
    for ($i = 0; $i <= $years_out; $i++) {
      $year = date('Y', strtotime("+{$i} year"));
      $year_options[$year] = $year;
    }

    $pane_form['payment_details']['cc_exp_year'] = [
      '#type' => 'select',
      '#title' => $this->t('Expiration Year'),
      '#options' => $year_options,
      '#default_value' => date('Y'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['cybersource-credit-card-exp-year-select', 'cybersource-input'],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // We don't send anything in payment here as that part is already processed.
    $cart = $this->getCart();
    $cart->clearPayment();
  }

}
