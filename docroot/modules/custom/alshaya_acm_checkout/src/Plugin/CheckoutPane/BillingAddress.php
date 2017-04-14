<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the billing address form.
 *
 * @ACQCheckoutPane(
 *   id = "billing_address",
 *   label = @Translation("Billing address"),
 *   default_step = "payment",
 *   wrapper_element = "container",
 * )
 */
class BillingAddress extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 10,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['billing_address']['summary'] = [
      '#markup' => 'Billing address form will come here',
    ];

    $pane_form['billing_address']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('place order'),
    ];

    return $pane_form;
  }

}
