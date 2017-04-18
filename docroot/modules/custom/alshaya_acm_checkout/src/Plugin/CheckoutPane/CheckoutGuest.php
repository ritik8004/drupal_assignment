<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides the login pane to checkout as guest.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_guest",
 *   label = @Translation("new customers"),
 *   default_step = "login",
 *   wrapper_element = "fieldset",
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
    $config = \Drupal::config('alshaya_acm_checkout.settings');

    $pane_form['checkout_guest']['checkout_as_guest'] = Link::createFromRoute($this->t('checkout as guest'), 'acq_checkout.form', ['step' => 'delivery'])->toRenderable();

    $pane_form['checkout_guest']['email_usage'] = [
      '#markup' => '<div class="checkout-guest-email-usage">' . $config->get('checkout_guest_email_usage.value') . '</div>',
    ];

    $pane_form['checkout_guest']['summary'] = [
      '#markup' => '<div class="checkout-guest-summary">' . $config->get('checkout_guest_summary.value') . '</div>',
    ];

    return $pane_form;
  }

}
