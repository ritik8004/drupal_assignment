<?php

namespace Drupal\alshaya_acm_checkout\Plugin\PaymentMethod;

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
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form = parent::buildPaneForm($pane_form, $form_state, $complete_form);

    $pane_form['payment_details']['cc_number']['#suffix'] = '
      <div class="card-types-wrapper">
        <span class="card-type card-type-visa"></span>
        <span class="card-type card-type-mastercard"></span>
        <span class="card-type card-type-diners-club"></span>
      </div>
    ';

    return $pane_form;
  }

}
