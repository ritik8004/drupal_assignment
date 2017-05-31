<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Login Tabs pane on Login page.
 *
 * @ACQCheckoutPane(
 *   id = "login_tabs",
 *   label = @Translation("Login Tabs"),
 *   defaultStep = "login",
 *   wrapperElement = "container",
 * )
 */
class LoginTabs extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => -20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // @TODO: We can think of making this dynamic if time permits.
    $pane_form['guest_checkout'] = [
      '#markup' => '<div class="tab tab-new-customer"><span>' . $this->t('guest checkout') . '</span></div>',
    ];

    $pane_form['returning_customer'] = [
      '#markup' => '<div class="tab tab-returning-customer"><span>' . $this->t('returning customers') . '</span></div>',
    ];

    return $pane_form;
  }

}
