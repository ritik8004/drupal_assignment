<?php

namespace Drupal\acq_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\ACQAddressFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Provides the contact information pane.
 *
 * @ACQCheckoutPane(
 *   id = "billing_information",
 *   label = @Translation("Billing information"),
 *   defaultStep = "billing",
 *   wrapperElement = "fieldset",
 * )
 */
class BillingInformation extends AddressFormBase {

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
  public function buildPaneSummary() {
    $cart = $this->getCart();
    $billing = $cart->getBilling();
    $address_formatter = new ACQAddressFormatter();
    return $address_formatter->render($billing);
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();
    $billing_address = $cart->getBilling();
    $form_state->setTemporaryValue('address', $billing_address);

    if (\Drupal::currentUser()->isAnonymous()) {
      $pane_form['email'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Email Address'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => [$this->t('Email Address')]],
      ];
    }

    $pane_form += parent::buildPaneForm($pane_form, $form_state, $complete_form);
    return $pane_form;
  }

  /**
   * Ajax handler for re-use address checkbox.
   */
  public static function updateAddressAjaxCallback($form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    $values = $values['billing_information'];
    $use_shipping_address = $values['use_shipping_address'];
    $address = $form_state->getTemporaryValue('address');
    $address_fields =& $form['billing_information']['address'];

    if (!empty($use_shipping_address)) {
      $address = $form_state->getTemporaryValue('shipping_address');
    }

    $field_names = [
      'first_name',
      'last_name',
      'telephone',
      'street',
      'street2',
    ];

    $dynamic_field_names = [
      'city',
      'region',
      'postcode',
      'country_id',
    ];

    foreach ($field_names as $field_name) {
      $address_fields[$field_name]['#value'] = $address->{$field_name} ?? '';
    }

    foreach ($dynamic_field_names as $field_name) {
      $address_fields['dynamic_parts'][$field_name]['#value'] = $address->{$field_name} ?? '';
    }

    return $address_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // @todo Add field validation.
    $values = $form_state->getValue($pane_form['#parents']);

    if (!isset($values['email'])) {
      return;
    }

    if (!(\Drupal::service('email.validator')->isValid($values['email']))) {
      $form_state->setErrorByName('email', $this->t('You have entered an invalid email addresss.'));
    }

    $user = user_load_by_mail($values['email']);

    if ($user !== FALSE) {
      $form_state->setErrorByName('email', $this->t('You already have an account, please login.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $address_values = $values['address'];
    $address = [];

    array_walk_recursive($address_values, function ($value, $key) use (&$address) {
      $address[$key] = $value;
    });

    $cart = $this->getCart();
    $cart->setBilling($address);

    // We are only looking to convert guest carts.
    if ($cart->customerId() != 0) {
      return;
    }

    $account = NULL;

    if (\Drupal::currentUser()->isAnonymous()) {
      $name = $values['address']['first_name'] . $values['address']['last_name'] . random_int(100, 999);

      $account = User::create(
        [
          'name' => $name,
          'mail' => $values['email'],
          'roles' => ['authenticated'],
          'status' => 1,
        ]
      );
      $account->save();
      user_login_finalize($account);
    }
    else {
      // We can't use a session, so full load user.
      $account = User::load(\Drupal::currentUser()->id());
    }

    if (empty($account->acq_customer_id->value)) {
      $customer = \Drupal::service('acq_commerce.api')
        ->createCustomer(
          $values['address']['first_name'],
          $values['address']['last_name'],
          $account->getEmail()
        );

      $account->acq_customer_id->value = $customer['customer_id'];
      $account->save();
    }

    $customer_cart = \Drupal::service('acq_commerce.api')
      ->createCart($account->acq_customer_id->value);

    $cart->convertToCustomerCart($customer_cart);

    \Drupal::service('acq_cart.cart_storage')->pushCart();
  }

}
