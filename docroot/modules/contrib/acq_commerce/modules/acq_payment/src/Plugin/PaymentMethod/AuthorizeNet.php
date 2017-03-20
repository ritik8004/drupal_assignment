<?php

/**
 * @file
 * Contains \Drupal\acq_payment\Plugin\PaymentMethod\AuthorizeNet.
 */

namespace Drupal\acq_payment\Plugin\PaymentMethod;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Authorize.net payment method.
 *
 * @ACQPaymentMethod(
 *   id = "authorizenet_directpost",
 *   label = @Translation("Credit Card"),
 * )
 */
class AuthorizeNet extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return 'Auth.net details here.';
  }

}
