<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the login pane to checkout as guest.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_guest",
 *   label = @Translation("Checkout as Guest"),
 *   default_step = "login",
 *   wrapper_element = "container",
 * )
 */
class CheckoutGuest extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['checkout_guest']['summary'] = [
      '#markup' => 'Continue as guest form will come here',
    ];

    $pane_form['checkout_guest']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('checkout out as guest'),
    ];

    return $pane_form;
  }

}
