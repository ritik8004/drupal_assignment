<?php

namespace Drupal\alshaya_acm_checkout\Plugin\PaymentMethod;

use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;

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

}
