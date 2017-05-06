<?php

namespace Drupal\alshaya_acm_knet\Plugin\PaymentMethod;

use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;

/**
 * Provides the K-Net payment method.
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
    return '';
  }

}
