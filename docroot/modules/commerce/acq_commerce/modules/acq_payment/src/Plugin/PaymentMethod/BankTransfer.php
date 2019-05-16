<?php

namespace Drupal\acq_payment\Plugin\PaymentMethod;

/**
 * Provides the `Bank Transfer` payment method.
 *
 * @ACQPaymentMethod(
 *   id = "banktransfer",
 *   label = @Translation("Bank Transfer"),
 * )
 */
class BankTransfer extends Cashondelivery {

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return $this->t('Bank transfer details here.');
  }

}
