<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutLoginTabsTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

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

  use CheckoutLoginTabsTrait;

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
    $pane_form['returning_customer'] = [
      '#markup' => '<span class="selected-tab-title mobile-only-block">' . $this->t('Sign In') . '</span>',
      '#weight' => -51,
    ];

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
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#size' => 15,
      '#maxlength' => 256,
      '#required' => TRUE,
      '#attributes' => [
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
      ],
      '#element_validate' => [
        'alshaya_valid_email_address',
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

    $complete_form['#attached']['library'][] = 'alshaya_user/email_validator_override';

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

    // If not valid email address.
    if (!\Drupal::service('email.validator')->isValid($mail)) {
      drupal_set_message($this->t('Username does not contain a valid email.'), 'error');
      $form_state->setErrorByName('custom', $this->t('Username does not contain a valid email.'));
      return;
    }

    try {
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
          drupal_set_message($this->t('Your account has not been activated or is blocked.', [], ['context' => 'alshaya_static_text|account_already_exists']), 'error');
          $form_state->setErrorByName('custom', $this->t('Your account has not been activated or is blocked.', [], ['context' => 'alshaya_static_text|account_already_exists']));
        }
      }
      else {
        drupal_set_message($this->t('Unrecognized email address or password.'), 'error');
        $form_state->setErrorByName('custom', $this->t('Unrecognized email address or password.'));
      }
    }
    catch (\Exception $e) {
      if (acq_commerce_is_exception_api_down_exception($e)) {
        $form_state->setErrorByName('custom', $e->getMessage());
      }
    }
  }

}
