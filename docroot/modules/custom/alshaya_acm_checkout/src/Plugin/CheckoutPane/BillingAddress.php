<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\AddressFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the billing address form.
 *
 * @ACQCheckoutPane(
 *   id = "billing_address",
 *   label = @Translation("Billing address"),
 *   defaultStep = "payment",
 *   wrapperElement = "fieldset",
 * )
 */
class BillingAddress extends AddressFormBase {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 10,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();

    $form_state->setTemporaryValue('address', $cart->getBilling());
    $form_state->setTemporaryValue('shipping_address', $cart->getShipping());

    $pane_form['summary'] = [
      '#markup' => $this->t('Is the delivery address the same as your billing address?'),
    ];

    $pane_form['same_as_shipping'] = [
      '#type' => 'radios',
      '#options' => [
        1 => $this->t('Yes'),
        2 => $this->t('No'),
      ],
      '#attributes' => ['class' => ['same-as-shipping']],
      '#ajax' => [
        'callback' => [$this, 'updateAddressAjaxCallback'],
        'wrapper' => 'address_wrapper',
      ],
    ];

    if ($form_state->getValues()) {
      $values = $form_state->getValue($pane_form['#parents']);
      $same_as_shipping = $values['same_as_shipping'];
      if ($same_as_shipping == 1) {
        $form_state->setTemporaryValue('address', $cart->getShipping());
      }
    }

    $pane_form += parent::buildPaneForm($pane_form, $form_state, $complete_form);

    $pane_form['address']['first_name']['#weight'] = -10;
    $pane_form['address']['last_name']['#weight'] = -9;
    $pane_form['address']['phone']['#weight'] = 0;

    return $pane_form;
  }

  /**
   * Ajax handler for reusing address.
   */
  public static function updateAddressAjaxCallback($form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    $values = $values['billing_address'];

    $same_as_shipping = $values['same_as_shipping'];
    $address = $form_state->getTemporaryValue('address');
    $address_fields =& $form['billing_address']['address'];

    if ($same_as_shipping == 1) {
      $address = $form_state->getTemporaryValue('shipping_address');
    }

    $field_names = [
      'first_name' => 'firstname',
      'last_name' => 'lastname',
      'phone' => 'phone',
      'street' => 'street',
      'street2' => 'street2',
    ];

    $dynamic_field_names = [
      'city',
      'region',
      'postcode',
      'country',
    ];

    foreach ($field_names as $field_key => $field_name) {
      $address_fields[$field_key]['#value'] = isset($address->{$field_name}) ? $address->{$field_name} : '';
    }

    foreach ($dynamic_field_names as $field_name) {
      $address_fields['dynamic_parts'][$field_name]['#value'] = isset($address->{$field_name}) ? $address->{$field_name} : '';
    }

    return $address_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();

    $values = $form_state->getValue($pane_form['#parents']);

    $address_values = $values['address'];
    $address = [];

    if ($form_state->getValue('same_as_shipping') == 1) {
      $address = $cart->getShipping();
    }
    else {
      array_walk_recursive($address_values, function ($value, $key) use (&$address) {
        $address[$key] = $value;
      });
    }

    $cart->setBilling($address);
  }

}
