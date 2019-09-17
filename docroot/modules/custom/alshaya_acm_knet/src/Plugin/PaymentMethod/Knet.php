<?php

namespace Drupal\alshaya_acm_knet\Plugin\PaymentMethod;

use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodBase;
use Drupal\acq_payment\Plugin\PaymentMethod\PaymentMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the K-Net payment method.
 *
 * @ACQPaymentMethod(
 *   id = "knet",
 *   label = @Translation("K-NET"),
 * )
 */
class Knet extends PaymentMethodBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    if ($knet_settings = \Drupal::config('alshaya_knet.settings')) {
      if ($knet_settings->get('resource_path')) {
        return TRUE;
      }
    }

    \Drupal::logger('alshaya_acm_knet')->critical('Please configure knet settings.');
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentSummary() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // K-Net doesn't need any payment details.
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaymentForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    /** @var \Drupal\alshaya_acm_checkout\CheckoutHelper $helper */
    $helper = \Drupal::service('alshaya_acm_checkout.checkout_helper');
    $helper->setSelectedPayment('knet', [], FALSE);
  }

}
