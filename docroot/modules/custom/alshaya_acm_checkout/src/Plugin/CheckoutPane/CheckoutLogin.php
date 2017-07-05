<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

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

    $pane_form['summary'] = [
      '#markup' => $config->get('checkout_guest_login.value'),
      '#weight' => -50,
    ];

    $pane_form['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -49,
    ];

    // Display login form:
    $pane_form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#size' => 15,
      '#maxlength' => UserInterface::USERNAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => [
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
      ],
    ];

    $pane_form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#size' => 15,
      '#description' => $this->t('Enter the password that accompanies your username.'),
      '#required' => TRUE,
      '#attached' => [
        'library' => ['alshaya_white_label/unmask_password'],
      ],
    ];

    $pane_form['actions'] = ['#type' => 'actions'];
    $pane_form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('sign in'),
    ];

    $pane_form['actions']['submit']['#attributes']['gtm-type'] = 'checkout-signin';

    // Get the forgot password link.
    $request_password_link = Url::fromRoute('user.pass', [], [
      'attributes' => [
        'title' => $this->t('Send password reset instructions via email.'),
        'class' => ['request-password-link'],
      ],
    ]);

    $pane_form['request_password'] = Link::fromTextAndUrl($this->t('Forgot password?'), $request_password_link)->toRenderable();
    $pane_form['request_password']['#weight'] = 101;

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getErrors()) {
      return;
    }

    $values = $form_state->getValue($pane_form['#parents']);
    $mail = $values['name'];
    $pass = $values['pass'];

    if ($uid = _alshaya_acm_customer_authenticate_customer($mail, $pass, TRUE)) {
      /** @var \Drupal\acq_cart\CartSessionStorage $cart_storage */
      $cart_storage = \Drupal::service('acq_cart.cart_storage');
      $cart_storage->getCart()->setCheckoutStep('delivery');

      $form_state->setRedirect('acq_checkout.form', ['step' => 'delivery']);

      $account = User::load($uid);

      if ($account->isActive()) {
        user_login_finalize($account);
      }
      else {
        drupal_set_message($this->t('Your account has not been activated or is blocked.'), 'error');
        $form_state->setErrorByName('custom', $this->t('Your account has not been activated or is blocked.'));
      }
    }
    else {
      drupal_set_message($this->t('Unrecognized email address or password.'), 'error');
      $form_state->setErrorByName('custom', $this->t('Unrecognized email address or password.'));
    }
  }

}
