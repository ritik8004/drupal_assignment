<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the delivery home pane for guests.
 *
 * @ACQCheckoutPane(
 *   id = "guest_delivery_home",
 *   label = @Translation("<h2>Home delivery</h2><p>Standard delivery for purchases over KD 250</p>"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class GuestDeliveryHome extends CheckoutPaneBase implements CheckoutPaneInterface {

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
    return ['weight' => 1] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if (\Drupal::currentUser()->isAuthenticated()) {
      return $pane_form;
    }

    $pane_form['#suffix'] = '<div class="fieldsets-separator">' . $this->t('OR') . '</div>';
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

    $address_storage = \Drupal::entityTypeManager()->getStorage('profile');
    $address_entity = $address_storage->create([
      'type' => 'address_book',
    ]);

    /** @var \Drupal\Core\Entity\EntityFormBuilder $entity_form_builder */
    $entity_form_builder = \Drupal::service('entity.form_builder');

    $address_form = $entity_form_builder->getForm($address_entity, 'add');

    $pane_form['address']['field_address'] = $address_form['field_address'];
    $pane_form['address']['field_address']['#weight'] = -5;

    $pane_form['address']['field_mobile_number'] = $address_form['field_mobile_number'];
    $pane_form['address']['field_address']['#weight'] = -4;

    $pane_form['address']['field_address_id'] = $address_form['field_address_id'];
    $pane_form['address']['field_address']['#weight'] = -3;

    $pane_form['address']['field_address']['widget'][0]['address']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => [$this->t('Email Address')]],
      '#default_value' => $cart->customerEmail(),
      '#weight' => -1,
    ];

    $shipping_methods = self::generateShippingEstimates($address);

    $default_shipping = $cart->getShippingMethodAsString();

    // Convert to code.
    $default_shipping = str_replace(',', '_', substr($default_shipping, 0, 32));

    if (!empty($shipping_methods) && empty($default_shipping)) {
      $default_shipping = array_keys($shipping_methods)[0];
    }

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'radios',
      '#title' => t('Shipping Methods'),
      '#default_value' => $default_shipping,
      '#validated' => TRUE,
      '#options' => $shipping_methods,
      '#prefix' => '<div id="shipping_methods_wrapper">',
      '#suffix' => '</div>',
    ];

    $complete_form['actions']['get_shipping_methods'] = [
      '#type' => 'button',
      '#value' => $this->t('deliver to this address'),
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

    $address_fields['shipping_methods']['#options'] = self::generateShippingEstimates($address);
    $address_fields['shipping_methods']['#default_value'] = array_keys($address_fields['shipping_methods']['#options'])[0];

    return $address_fields;
  }

  /**
   * Helper function to get shipping estimates.
   *
   * @param array|object $address
   *   Array of object of address.
   *
   * @return array
   *   Available shipping methods.
   */
  public static function generateShippingEstimates($address) {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_checkout', 'inc', 'alshaya_acm_checkout.shipping');
    $address = (array) $address;

    $address = _alshaya_acm_checkout_clean_address($address);

    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();

    $shipping_methods = [];
    $shipping_method_options = [];

    if (!empty($address) && !empty($address['country'])) {
      $shipping_methods = \Drupal::service('acq_commerce.api')->getShippingEstimates($cart->id(), $address);
    }

    if (!empty($shipping_methods)) {
      foreach ($shipping_methods as $method) {
        // Key needs to hold both carrier and method.
        $key = implode('_', [$method['carrier_code'], $method['method_code']]);

        // @TODO: Currently what we get back in orders is first 32 characters
        // and concatenated by underscore.
        $code = substr($key, 0, 32);
        $name = t('@method_title by @carrier_title', ['@method_title' => $method['method_title'], '@carrier_title' => $method['carrier_title']]);
        $price = !empty($method['amount']) ? alshaya_acm_price_format($method['amount']) : t('FREE');

        $term = alshaya_acm_checkout_load_shipping_method($code, $name, $method['carrier_code'], $method['method_code']);

        // We don't display click and collect delivery method for home delivery.
        if ($code == \Drupal::config('alshaya_acm_checkout.settings')->get('click_collect_method')) {
          continue;
        }

        $method_name = '
          <div class="shipping-method-name">
            <div class="shipping-method-title">' . $term->getName() . '</div>
            <div class="shipping-method-price">' . $price . '</div>
            <div class="shipping-method-description">' . $term->get('description')->getValue()[0]['value'] . '</div>
          </div>
        ';

        $shipping_method_options[$code] = $method_name;
      }
    }

    return $shipping_method_options;
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
      $form_state->setErrorByName('guest_delivery_home][email', $this->t('You already have an account, please login.'));
    }

    if ($form_state->getErrors()) {
      return;
    }

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

    // Store in separate variable to use later, will be removed while cleaning.
    $email = $address['email'];

    $cart->setShipping(_alshaya_acm_checkout_clean_address($address));

    if (empty($shipping_method)) {
      return;
    }

    $term = alshaya_acm_checkout_load_shipping_method($shipping_method);

    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString());

    // We are only looking to convert guest carts.
    if (!($cart->customerId())) {
      // Get the customer id of Magento from this email.
      /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
      $api_wrapper = \Drupal::service('acq_commerce.api');

      try {
        $customer = $api_wrapper->createCustomer($address['first_name'], $address['last_name'], $email);
      }
      catch (\Exception $e) {
        // @TODO: Handle create customer errors here.
        // Probably just the email error.
        \Drupal::logger('alshaya_acm_checkout')->error('Error while creating customer for guest cart: @message', ['@message' => $e->getMessage()]);
        $form_state->setErrorByName('custom', '');
        drupal_set_message($this->t('Something looks wrong, please try again later.'), 'error');
        return;
      }

      $customer_cart = $api_wrapper->createCart($customer['customer_id']);

      if (empty($customer_cart['customer_email'])) {
        $customer_cart['customer_email'] = $email;
      }

      $cart->convertToCustomerCart($customer_cart);
      \Drupal::service('acq_cart.cart_storage')->addCart($cart);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // We have done everything in validatePaneForm().
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
