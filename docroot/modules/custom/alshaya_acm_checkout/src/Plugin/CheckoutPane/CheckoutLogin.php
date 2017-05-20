<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides the login form pane.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_login",
 *   label = @Translation("returning customers"),
 *   defaultStep = "login",
 *   wrapperElement = "fieldset",
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
    $config = \Drupal::config('alshaya_acm_checkout.settings');

    $pane_form['checkout_login']['summary'] = [
      '#markup' => $config->get('checkout_guest_login.value'),
    ];

    $pane_form['checkout_login']['form'] = \Drupal::formBuilder()->getForm('Drupal\user\Form\UserLoginForm');

    // When unsetting field descriptions, also unset aria-describedby attributes
    // to avoid introducing an accessibility bug.
    unset($pane_form['checkout_login']['form']['name']['#description']);
    unset($pane_form['checkout_login']['form']['name']['#attributes']['aria-describedby']);
    unset($pane_form['checkout_login']['form']['pass']['#description']);
    unset($pane_form['checkout_login']['form']['pass']['#attributes']['aria-describedby']);

    // Use small size for input elements.
    $pane_form['checkout_login']['form']['name']['#size'] = 15;
    $pane_form['checkout_login']['form']['pass']['#size'] = 15;

    // Get the forgot password link.
    $request_password_link = Url::fromRoute('user.pass', [], [
      'attributes' => [
        'title' => $this->t('Send password reset instructions via email.'),
        'class' => ['request-password-link'],
      ],
    ]);

    $pane_form['checkout_login']['form']['request_password'] = Link::fromTextAndUrl($this->t('Forgot password'), $request_password_link)->toRenderable();
    $pane_form['checkout_login']['form']['request_password']['#weight'] = 101;

    // Remove all other links.
    unset($pane_form['checkout_login']['form']['account']);

    return $pane_form;
  }

}
