<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutFlow;

use Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowWithPanesBase;

/**
 * Provides the default multistep checkout flow.
 *
 * @ACQCheckoutFlow(
 *   id = "multistep_checkout",
 *   label = "Multistep Checkout",
 * )
 */
class MultistepCheckout extends CheckoutFlowWithPanesBase {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    $steps = [];
    if (\Drupal::currentUser()->isAnonymous()) {
      $steps['login'] = [
        'label' => $this->t('Login'),
        'previous_label' => $this->t('Return to login'),
      ];
    }

    $steps['delivery'] = [
      'label' => $this->t('Choose delivery'),
      'next_label' => $this->t('Continue to delivery options'),
      'previous_label' => $this->t('Return to delivery options'),
    ];

    $steps['payment'] = [
      'label' => $this->t('Make payment'),
      'next_label' => $this->t('Continue to payment'),
    ];

    $steps['confirmation'] = [
      'label' => $this->t('Order confirmation'),
      'next_label' => $this->t('View order confirmation'),
    ];

    return $steps;
  }

}
