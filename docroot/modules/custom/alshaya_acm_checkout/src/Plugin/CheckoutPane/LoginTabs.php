<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutLoginTabsTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the Login Tabs pane on Login page.
 *
 * @ACQCheckoutPane(
 *   id = "login_tabs",
 *   label = @Translation("Login Tabs"),
 *   defaultStep = "_disabled",
 *   wrapperElement = "container",
 * )
 */
class LoginTabs extends CheckoutPaneBase implements CheckoutPaneInterface {

  use CheckoutLoginTabsTrait;

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
    if ($this->getSelectedTab() === 'login') {
      return $pane_form;
    }

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery']);

    $guest_checkout = '<div class="tab tab-new-customer">';
    $guest_checkout .= '<a href="' . $url->toString() . '" gtm-type="checkout-as-guest">';
    $guest_checkout .= '<span>' . $this->t('checkout as guest') . '</span>';
    $guest_checkout .= '</a></div>';

    $pane_form['guest_checkout'] = [
      '#markup' => $guest_checkout,
    ];

    $pane_form['separator'] = [
      '#markup' => '<div class="tab tab-separator">' . $this->t('OR') . '</div>',
    ];

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'login']);
    // Set login as tab in params for checkout login.
    $url->setRouteParameter('tab', 'login');

    $checkout_login = '<div class="tab tab-returning-customer">';
    $checkout_login .= '<a href="' . $url->toString() . '">';
    $checkout_login .= '<span>' . $this->t('Returning customer? SIGN IN') . '</span>';
    $checkout_login .= '</a></div>';

    $pane_form['returning_customer'] = [
      '#markup' => $checkout_login,
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
      '#weight' => 99,
    ];

    return $pane_form;
  }

}
