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

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    $pane_form['#suffix'] = '<div class="fieldsets-separator">' . $this->t('OR') . '</div>';
    $pane_form['guest_delivery_home']['title'] = [
      '#markup' => '<div class="title">' . $this->t('delivery information') . '</div>',
    ];

    $cart = $this->getCart();
    $address = (array) $cart->getShipping();

    if (empty($address['country_id'])) {
      $address_default_value = [
        'country_code' => _alshaya_custom_get_site_level_country_code(),
      ];
    }
    else {
      $address_default_value = $address_book_manager->getAddressArrayFromMagentoAddress($address);

      if (!empty($address_default_value['mobile_number'])) {
        $form_state->setTemporaryValue('default_value_mobile', $address_default_value['mobile_number']);
        unset($address_default_value['mobile_number']);
      }

      $address_default_value['organization'] = $cart->customerEmail();
    }

    if ($form_values = $form_state->getValue($pane_form['#parents'])) {
      $address = $address_book_manager->getMagentoAddressFromAddressArray($form_values['address']['shipping']);
    }

    if ($email = $cart->customerEmail()) {
      $address_default_value['organization'] = $email;
    }

    $pane_form['address'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['address_wrapper'],
      ],
    ];

    $pane_form['address']['shipping'] = [
      '#type' => 'address',
      '#title' => '',
      '#default_value' => $address_default_value,
      '#require_email' => TRUE,
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
    $address_fields =& $form['guest_delivery_home']['address'];

    if (!$form_state->getErrors()) {
      /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
      $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

      $values = $form_state->getValue($form['#parents']);
      $address_values = $values['guest_delivery_home']['address']['shipping'];
      $address = $address_book_manager->getMagentoAddressFromAddressArray($address_values);

      $address_fields['shipping_methods']['#options'] = self::generateShippingEstimates($address);
      $address_fields['shipping_methods']['#default_value'] = array_keys($address_fields['shipping_methods']['#options'])[0];
    }

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
    // Below code is to ensure we call the API only once.
    static $options;
    $static_key = base64_encode(serialize($address));
    if (isset($options[$static_key]) && !empty($options[$static_key])) {
      return $options[$static_key];
    }

    $address = (array) $address;

    $address = _alshaya_acm_checkout_clean_address($address);

    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();

    $shipping_methods = [];
    $shipping_method_options = [];

    if (!empty($address) && !empty($address['country_id'])) {
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

        /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
        $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
        $term = $checkout_options_manager->loadShippingMethod($code, $name, $method['carrier_code'], $method['method_code']);

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

    $options[$static_key] = $shipping_method_options;

    return $shipping_method_options;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getErrors()) {
      return;
    }

    $values = $form_state->getValue($pane_form['#parents']);
    $address_values = $values['address']['shipping'];
    $email = $address_values['organization'];

    /** @var \Drupal\profile\Entity\Profile $profile */
    $profile = \Drupal::entityTypeManager()->getStorage('profile')->create([
      'type' => 'address_book',
      'uid' => 0,
      'field_address' => $address_values,
    ]);

    /* @var \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations */
    if ($violations = $profile->validate()) {
      foreach ($violations->getByFields(['field_address']) as $violation) {
        $error_field = explode('.', $violation->getPropertyPath());
        $form_state->setErrorByName('guest_delivery_home][address][address][' . $error_field[2], $violation->getMessage());
      }
    }

    if ($form_state->getErrors()) {
      return;
    }

    if ($user = user_load_by_mail($email)) {
      $form_state->setErrorByName('guest_delivery_home[address][shipping][email', $this->t('You already have an account, please login.'));
      return;
    }

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');
    $address = $address_book_manager->getMagentoAddressFromAddressArray($address_values);

    $cart = $this->getCart();
    $cart->setShipping(_alshaya_acm_checkout_clean_address($address));

    $shipping_method = NULL;

    if (isset($values['address']['shipping_methods'])) {
      $shipping_method = $values['address']['shipping_methods'];
      unset($values['address']['shipping_methods']);
    }

    if (empty($shipping_method)) {
      return;
    }

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    $term = $checkout_options_manager->loadShippingMethod($shipping_method);

    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString());

    // We are only looking to convert guest carts.
    if (!($cart->customerId())) {
      // Get the customer id of Magento from this email.
      /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
      $api_wrapper = \Drupal::service('acq_commerce.api');

      try {
        $customer = $api_wrapper->createCustomer($address['firstname'], $address['lastname'], $email);
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

}
