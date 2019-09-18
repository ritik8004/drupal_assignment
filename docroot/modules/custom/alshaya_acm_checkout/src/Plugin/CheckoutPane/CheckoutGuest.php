<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutLoginTabsTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides the login pane to checkout as guest.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_guest",
 *   label = @Translation("new customer?"),
 *   defaultStep = "login",
 *   wrapperElement = "fieldset",
 * )
 */
class CheckoutGuest extends CheckoutPaneBase implements CheckoutPaneInterface {

  use CheckoutLoginTabsTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 3,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $config = \Drupal::config('alshaya_acm_checkout.settings');
    $checkout_guest_options = [
      'attributes' => [
        'gtm-type' => 'checkout-as-guest',
      ],
    ];

    $link = Link::createFromRoute(
      $this->t('checkout as guest'),
      'acq_checkout.form',
      ['step' => 'delivery'],
      $checkout_guest_options
    );

    $pane_form['#prefix'] = '<div class="checkout-login-separator order-5"><span>' . $this->t('or') . '</span></div>';
    $pane_form['checkout_as_guest'] = $link->toRenderable();
    $pane_form['checkout_as_guest']['#prefix'] = '<div class="above-mobile-block">';
    $pane_form['checkout_as_guest']['#suffix'] = '</div>';

    if (!empty($config->get('checkout_guest_email_usage.value'))) {
      $pane_form['email_usage'] = [
        '#markup' => '<div class="checkout-guest-email-usage">' . $config->get('checkout_guest_email_usage.value') . '</div>',
      ];
    }

    if (!empty($config->get('checkout_guest_summary.value'))) {
      $pane_form['summary'] = [
        '#markup' => '<div class="checkout-guest-summary">' . $config->get('checkout_guest_summary.value') . '</div>',
      ];
    }

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
