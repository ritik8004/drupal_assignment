<?php

namespace Drupal\acq_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\ACQAddressFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the contact information pane.
 *
 * @ACQCheckoutPane(
 *   id = "shipping_information",
 *   label = @Translation("Shipping information"),
 *   defaultStep = "shipping",
 *   wrapperElement = "fieldset",
 * )
 */
class ShippingInformation extends AddressFormBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 0,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    // @todo Uncomment once cart sets if items are shippable or not.
    // $cart = $this->getCart();
    // return $cart->getShippable();
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $cart = $this->getCart();
    $shipping = $cart->getShipping();
    $address_formatter = new ACQAddressFormatter();
    return $address_formatter->render($shipping);
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();
    $address = $cart->getShipping();
    $billing_address = $cart->getBilling();

    $form_state->setTemporaryValue('address', $address);
    $form_state->setTemporaryValue('billing_address', $billing_address);

    $pane_form['use_billing_address'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use same address as billing'),
      '#default_value' => '',
      '#ajax' => [
        'callback' => $this->updateAddressAjaxCallback(...),
        'wrapper' => 'address_wrapper',
      ],
    ];

    $pane_form += parent::buildPaneForm($pane_form, $form_state, $complete_form);

    // Block proceeding checkout until shipping method is chosen.
    $complete_form['actions']['next']['#states'] = [
      'invisible' => [
        '#shipping_methods_wrapper' => ['value' => ''],
      ],
    ];

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'select',
      '#title' => $this->t('Shipping Methods'),
      '#empty_option' => $this->t('Available Shipping Methods'),
      '#default_value' => $cart->getShippingMethodAsString(),
      '#validated' => TRUE,
      '#attributes' => [
        'id' => ['shipping_methods_wrapper'],
      ],
    ];

    ShippingInformation::generateShippingEstimates(
      $address,
      $pane_form['address']['shipping_methods']
    );

    $complete_form['actions']['get_shipping_methods'] = [
      '#type' => 'button',
      '#value' => $this->t('Estimate Shipping'),
      '#ajax' => [
        'callback' => $this->updateAddressAjaxCallback(...),
        'wrapper' => 'address_wrapper',
      ],
      '#weight' => -50,
    ];

    return $pane_form;
  }

  /**
   * Ajax handler for re-use address checkbox.
   */
  public static function updateAddressAjaxCallback($form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    $values = $values['shipping_information'];

    $use_billing_address = $values['use_billing_address'];
    $address = $form_state->getTemporaryValue('address');
    $address_fields =& $form['shipping_information']['address'];

    if (!empty($use_billing_address)) {
      $address = $form_state->getTemporaryValue('billing_address');
    }

    $field_names = [
      'first_name' => 'firstname',
      'last_name' => 'lastname',
      'telephone' => 'telephone',
      'street' => 'street',
      'street2' => 'street2',
    ];

    $dynamic_field_names = [
      'city',
      'region',
      'postcode',
      'country_id',
    ];

    foreach ($field_names as $field_key => $field_name) {
      $address_fields[$field_key]['#value'] = $address->{$field_name} ?? '';
    }

    foreach ($dynamic_field_names as $field_name) {
      $address_fields['dynamic_parts'][$field_name]['#value'] = $address->{$field_name} ?? '';
    }

    if (empty($use_billing_address)) {
      $values = $form_state->getValue('shipping_information')['address'];

      $address = [
        'region' => $values['dynamic_parts']['region'],
        'country_id' => $values['dynamic_parts']['country_id'],
        'street' => $values['street'],
        'street2' => $values['street2'],
        'telephone' => $values['telephone'],
        'postcode' => $values['dynamic_parts']['postcode'],
        'city' => $values['dynamic_parts']['city'],
        'firstname' => $values['first_name'],
        'lastname' => $values['last_name'],
      ];
    }

    ShippingInformation::generateShippingEstimates(
      $address,
      $address_fields['shipping_methods']
    );

    return $address_fields;
  }

  /**
   * Generates shipping estimate based on address and chosen shipping method.
   *
   * @param array|object $address
   *   The object of address.
   * @param array $select
   *   Array with selected shipping method.
   */
  public static function generateShippingEstimates($address, array &$select) {
    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();

    $shipping_methods = \Drupal::service('acq_commerce.api')
      ->getShippingEstimates($cart->id(), $address);

    if (empty($shipping_methods)) {
      $select['#suffix'] = t('No shipping methods found, please check your address and try again.');
      return;
    }
    else {
      unset($select['#suffix']);
    }

    foreach ($shipping_methods as $method) {
      // Key needs to hold both carrier and method.
      $key = implode(
        ',',
        [
          $method['carrier_code'],
          $method['method_code'],
        ]
      );

      $name = t(
        '@carrier — @method (@price)',
        [
          '@carrier' => $method['carrier_title'],
          '@method' => $method['method_title'],
          '@price' => $method['amount'] ?: t('Free'),
        ]
      );

      $select['#options'][$key] = $name;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);

    $shipping_method = NULL;

    if (isset($values['address']['shipping_methods'])) {
      $shipping_method = $values['address']['shipping_methods'];
      unset($values['address']['shipping_methods']);
    }

    $address_values = $values['address'];
    $address = [];

    array_walk_recursive($address_values, function ($value, $key) use (&$address) {
      $address[$key] = $value;
    });

    $cart = $this->getCart();
    $cart->setShipping($address);

    if (empty($shipping_method)) {
      return;
    }

    [$carrier, $method] = explode(',', $shipping_method);

    $cart->setShippingMethod($carrier, $method);
  }

}
