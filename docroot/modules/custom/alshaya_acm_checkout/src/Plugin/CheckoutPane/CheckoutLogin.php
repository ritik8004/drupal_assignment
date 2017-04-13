<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the login form pane.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_login",
 *   label = @Translation("Login to checkout"),
 *   default_step = "login",
 *   wrapper_element = "container",
 * )
 */
class CheckoutLogin extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 2,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['checkout_login']['summary'] = [
      '#markup' => 'Login form to continue as member will come here',
    ];

    $pane_form['checkout_login']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('sign in'),
    ];

    return $pane_form;
  }

}
