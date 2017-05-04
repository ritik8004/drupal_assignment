<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\AddressFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mobile_number\MobileNumberUtilInterface;

/**
 * Provides the delivery home pane for guests.
 *
 * @ACQCheckoutPane(
 *   id = "guest_delivery_home",
 *   label = @Translation("Home delivery"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class GuestDeliveryHome extends AddressFormBase {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAnonymous();
  }

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
    $pane_form['guest_delivery_home']['summary'] = [
      '#markup' => $this->t('Standard delivery for purchases over KD 250'),
    ];

    $pane_form['guest_delivery_home']['title'] = [
      '#markup' => '<div class="title">' . $this->t('delivery information') . '</div>',
    ];

    $cart = $this->getCart();
    $address = $cart->getShipping();

    if ($form_values = $form_state->getValue($pane_form['#parents'])) {
      $address = self::getAddressFromValues($form_values['address']);
    }

    $address_object = (object) $address;

    if (empty($address_object->country)) {
      $address_object = NULL;
    }

    $form_state->setTemporaryValue('address', $address_object);

    $pane_form += parent::buildPaneForm($pane_form, $form_state, $complete_form);

    // Do required changes in weight.
    $pane_form['address']['first_name']['#weight'] = -10;
    $pane_form['address']['last_name']['#weight'] = -9;
    $pane_form['address']['phone']['#weight'] = 0;

    // Update the phone number to mobile_number field instead of textfield.
    $pane_form['address']['phone']['#type'] = 'mobile_number';
    $pane_form['address']['phone']['#title_display'] = 'above';
    $pane_form['address']['phone']['#verify'] = MobileNumberUtilInterface::MOBILE_NUMBER_VERIFY_NONE;
    $pane_form['address']['phone']['#default_value'] = [
      'value' => $pane_form['address']['phone']['#default_value'],
    ];

    // Fix default value for region.
    if ($region = $pane_form['address']['dynamic_parts']['region']['#default_value']) {
      $region_options = $pane_form['address']['dynamic_parts']['region']['#options'];

      if (!isset($region_options[$region])) {
        if ($region_key = array_search($region, $region_options)) {
          $pane_form['address']['dynamic_parts']['region']['#default_value'] = $region_key;
        }
      }
    }

    // Block proceeding checkout until shipping method is chosen.
    $complete_form['actions']['next']['#states'] = [
      'invisible' => [
        '#shipping_methods_wrapper' => ['value' => ''],
      ],
    ];

    $pane_form['address']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => [$this->t('Email Address')]],
      '#weight' => -8,
      '#default_value' => !empty($address->email) ? $address->email : '',
    ];

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'select',
      '#title' => t('Shipping Methods'),
      '#empty_option' => t('Available Shipping Methods'),
      '#default_value' => $cart->getShippingMethodAsString(),
      '#validated' => TRUE,
      '#attributes' => [
        'id' => ['shipping_methods_wrapper'],
      ],
    ];

    GuestDeliveryHome::generateShippingEstimates(
      $address,
      $pane_form['address']['shipping_methods']
    );

    $complete_form['actions']['get_shipping_methods'] = [
      '#type' => 'button',
      '#value' => $this->t('Estimate Shipping'),
      '#ajax' => [
        'callback' => [$this, 'updateAddressAjaxCallback'],
        'wrapper' => 'address_wrapper',
      ],
      '#weight' => -50,
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

  /**
   * Ajax handler for re-use address checkbox.
   */
  public static function updateAddressAjaxCallback($form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);

    $address_fields =& $form['guest_delivery_home']['address'];

    $address_values = $values['guest_delivery_home']['address'];
    unset($address_values['email']);
    $address = self::getAddressFromValues($address_values);

    self::generateShippingEstimates(
      $address,
      $address_fields['shipping_methods']
    );

    return $address_fields;
  }

  /**
   * Helper function to get shipping estimates.
   *
   * @param array|object $address
   *   Array of object of address.
   * @param array $select
   *   Selected method.
   */
  public static function generateShippingEstimates($address, array &$select) {
    $address = (array) $address;

    $address = _alshaya_acm_checkout_clean_address($address);

    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();

    $shipping_methods = [];

    if (!empty($address) && !empty($address['country'])) {
      $shipping_methods = \Drupal::service('acq_commerce.api')->getShippingEstimates($cart->id(), $address);
    }

    if (empty($shipping_methods)) {
      $select['#suffix'] = t('No shipping methods found, please check your address and try again.');
      return;
    }
    else {
      unset($select['#suffix']);
    }

    foreach ($shipping_methods as $method) {
      // Key needs to hold both carrier and method.
      $key = implode(',', [$method['carrier_code'], $method['method_code']]);

      $name = t(
        '@carrier — @method (@price)',
        [
          '@carrier' => $method['carrier_title'],
          '@method' => $method['method_title'],
          '@price' => $method['amount'] ? $method['amount'] : t('Free'),
        ]
      );

      $select['#options'][$key] = $name;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);

    if (!(\Drupal::service('email.validator')->isValid($values['address']['email']))) {
      $form_state->setErrorByName('email', $this->t('You have entered an invalid email addresss.'));
    }

    $user = user_load_by_mail($values['address']['email']);

    if ($user !== FALSE) {
      $form_state->setErrorByName('email', $this->t('You already have an account, please login.'));
    }
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

    if (!empty($address_values['phone'])) {
      $address_values['phone'] = _alshaya_acm_checkout_clean_address_phone($address_values['phone']);
    }

    $address = [];

    array_walk_recursive($address_values, function ($value, $key) use (&$address) {
      $address[$key] = $value;
    });

    $cart = $this->getCart();
    $cart->setShipping(_alshaya_acm_checkout_clean_address($address));

    if (empty($shipping_method)) {
      return;
    }

    list($carrier, $method) = explode(',', $shipping_method);

    $cart->setShippingMethod($carrier, $method);

    // We are only looking to convert guest carts.
    if ($cart->customerId() != 0) {
      return;
    }

    // Get the customer id of Magento from this email.
    $customer = \Drupal::service('acq_commerce.api')->createCustomer($address['first_name'], $address['last_name'], $address['email']);
    $customer_cart = \Drupal::service('acq_commerce.api')->createCart($customer['customer_id']);
    $cart->convertToCustomerCart($customer_cart);
    \Drupal::service('acq_cart.cart_storage')->pushCart();
  }

  /**
   * Helper function to get address from form_state values.
   *
   * @param array $address_values
   *   Array containing values.
   *
   * @return array
   *   Address array.
   */
  public static function getAddressFromValues(array $address_values) {
    $address = [];

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
      $address[$field_name] = $address_values[$field_key];
    }

    foreach ($dynamic_field_names as $field_name) {
      $address[$field_name] = $address_values['dynamic_parts'][$field_name];
    }

    if (!empty($address['phone'])) {
      $address['phone'] = _alshaya_acm_checkout_clean_address_phone($address['phone']);
    }

    return $address;
  }

}
