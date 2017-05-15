<?php

namespace Drupal\acq_payment\Plugin\PaymentMethod;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Cash on Delivery payment method.
 *
 * @ACQPaymentMethod(
 *   id = "cashondelivery",
 *   label = @Translation("Cash on Delivery"),
 * )
 */
class Cashondelivery extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('Cash on Delivery details here.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Cash on Delivery doesn't need any payment details.
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();
    $cart->setPaymentMethodData([
      'cc_type' => '',
    ]);
  }

}
