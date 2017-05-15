<?php

namespace Drupal\acq_payment\Plugin\PaymentMethod;

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
    $pane_form = parent::buildPaneForm($pane_form, $form_state, $complete_form);

    return $pane_form;
  }

}
