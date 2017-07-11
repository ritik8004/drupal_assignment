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
      '#attached' => [
        'library' => [
          'core/drupal.form',
          'alshaya_white_label/convert_to_select2',
          'clientside_validation_jquery/cv.jquery.validate',
        ],
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
    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    $default_shipping = $checkout_options_manager->getCleanShippingMethodCode($default_shipping);

    if (!empty($shipping_methods) && empty($default_shipping)) {
      $default_shipping = array_keys($shipping_methods)[0];
    }

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'radios',
      '#title' => $this->t('select delivery options'),
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
      '#limit_validation_errors' => [['address']],
    ];

    $complete_form['actions']['next']['#limit_validation_errors'] = [['address']];

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
    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    return $checkout_options_manager->getHomeDeliveryShippingEstimates($address);
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getValue('selected_tab') != 'checkout-home-delivery') {
      return;
    }

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

    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString(), []);

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

}
