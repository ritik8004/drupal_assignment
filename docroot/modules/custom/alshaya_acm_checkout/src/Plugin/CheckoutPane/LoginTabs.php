<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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

    $pane_form['separator'] = [
      '#markup' => '<div class="tab tab-separator">' . $this->t('OR') . '</div>',
    ];

    $pane_form['returning_customer'] = [
      '#markup' => '<div class="tab tab-returning-customer"><span>' . $this->t('returning customers') . '</span></div>',
    ];

    $complete_form['actions'] = [
      '#type' => 'actions',
      '#weight' => 100,
      '#attributes' => [
        'class' => ['checkout-login-actions-wrapper'],
      ],
    ];

    $complete_form['actions']['back_to_basket'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to basket'),
      '#url' => Url::fromRoute('acq_cart.cart'),
      '#attributes' => [
        'class' => ['back-to-basket'],
      ],
    ];

    return $pane_form;
  }

}
