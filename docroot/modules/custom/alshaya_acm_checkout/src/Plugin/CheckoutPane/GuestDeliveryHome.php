<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutDeliveryMethodTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
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
  // Add trait to get selected delivery method tab.
  use CheckoutDeliveryMethodTrait;

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAnonymous() || !alshaya_acm_customer_is_customer(\Drupal::currentUser());
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
    if ($this->getSelectedDeliveryMethod() != 'hd') {
      return $pane_form;
    }

    $pane_form['#attributes']['class'][] = 'active--tab--content';

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

    $pane_form['#suffix'] = '<div class="fieldsets-separator">' . $this->t('OR') . '</div>';
    $pane_form['guest_delivery_home']['title'] = [
      '#markup' => '<div class="title">' . $this->t('delivery information') . '</div>',
    ];

    // Check if user is changing their mind, if so clear shipping info.
    if ($this->isUserChangingHisMind()) {
      $this->clearShippingInfo();
    }

    $cart = $this->getCart();

    $address_info = $this->getAddressInfo('hd');
    $address = !empty($address_info['address']) ? $address_info['address'] : [];

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

    $default_shipping = '';
    if ($form_values = $form_state->getValue($pane_form['#parents'])) {
      $address = $address_book_manager->getMagentoAddressFromAddressArray($form_values['address']['shipping']);

      if (!empty($form_values['address']['shipping_methods'])) {
        $default_shipping = $form_values['address']['shipping_methods'];
      }
    }
    else {
      $default_shipping = $checkout_options_manager->getCleanShippingMethodCode($cart->getShippingMethodAsString());
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

    $shipping_methods = [];

    // This is getting very messy but required for the moment, we need to look
    // for better approach here.
    // Issue: below code is tightly plugged with click and collect.
    if ($default_shipping == $checkout_options_manager->getClickandColectShippingMethod()) {
      $default_shipping = '';
    }
    else {
      // We call generate shipping estimates only if we are not using click and
      // method as of now.
      $shipping_methods = self::generateShippingEstimates($address);

      // Fetch translations for shipping methods.
      $shipping_method_translations = $checkout_options_manager->getShippingMethodTranslations();

      if (!empty($shipping_methods) && empty($default_shipping)) {
        $default_shipping = array_keys($shipping_methods)[0];
      }
    }

    $selected_address = '';

    if ($shipping_methods) {
      // If shipping method available, we need to update/change the title.
      $pane_form['guest_delivery_home']['title'] = [
        '#markup' => '<div class="title">' . $this->t('deliver to') . '</div>',
      ];

      $drupal_address = $address_book_manager->getAddressArrayFromMagentoAddress($address);

      $change_address_button = [
        '#type' => 'link',
        '#title' => $this->t('Change'),
        '#url' => Url::fromRoute('<none>'),
        '#attributes' => [
          'id' => 'change-address',
          'class' => ['button'],
        ],
      ];

      $selected_address_build = [
        '#theme' => 'checkout_selected_address',
        '#delivery_to' => $drupal_address['given_name'] . ' ' . $drupal_address['family_name'],
        '#delivery_address' => $drupal_address,
        '#contact_no' => $drupal_address['mobile_number']['value'],
        '#change_address' => render($change_address_button),
      ];

      GuestDeliveryHome::exposeSelectedDeliveryAddressToGtm($drupal_address);

      $selected_address = '<div id="selected-address-wrapper">' . render($selected_address_build) . '</div>';
    }

    $pane_form['address']['selected_address'] = [
      '#markup' => $selected_address,
    ];

    $shipping_methods_count_class = 'shipping-method-options-count-' . count($shipping_methods);

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'radios',
      '#title' => count($shipping_methods) == 1 ? $this->t('delivery option') : $this->t('select delivery options'),
      '#default_value' => $default_shipping,
      '#validated' => TRUE,
      '#options' => $shipping_methods,
      '#prefix' => '<div id="shipping_methods_wrapper" class="' . $shipping_methods_count_class . '">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => [
          'shipping-methods-container',
        ],
      ],
    ];

    if (!empty($shipping_method_translations)) {
      $pane_form['address']['shipping_methods']['#attached']['drupalSettings']['alshaya_shipping_method_translations'] = $shipping_method_translations;
    }

    $complete_form['actions']['get_shipping_methods'] = [
      '#type' => 'button',
      '#value' => $this->t('deliver to this address'),
      '#ajax' => [
        'callback' => [$this, 'updateAddressAjaxCallback'],
        'wrapper' => 'address_wrapper',
      ],
      '#submit' => [],
      '#weight' => -50,
      '#limit_validation_errors' => [
        ['guest_delivery_home', 'address', 'shipping'],
      ],
    ];

    $complete_form['actions']['next']['#limit_validation_errors'] = [
      ['guest_delivery_home', 'address', 'shipping'],
      ['guest_delivery_home', 'address', 'shipping_methods'],
    ];

    $complete_form['actions']['next']['#attributes']['class'][] = 'delivery-home-next';

    return $pane_form;
  }

  /**
   * Ajax handler for re-use address checkbox.
   */
  public static function updateAddressAjaxCallback($form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $address_fields =& $form['guest_delivery_home']['address'];

    if ($form_state->getErrors()) {
      $address_fields['shipping_methods']['#access'] = FALSE;
      $address_fields['selected_address']['#access'] = FALSE;

      $response->addCommand(new ReplaceCommand('#address_wrapper', $address_fields));
      $response->addCommand(new InvokeCommand(NULL, 'firstErrorFocus', ['form.multistep-checkout .address-book-address', TRUE]));
    }
    else {
      // Clear the shipping method info now to ensure we set it properly again.
      \Drupal::service('acq_cart.cart_storage')->clearShippingMethodSession();

      $plugin_id = 'guest_delivery_home';
      \Drupal::moduleHandler()->alter(
        'home_delivery_save_address',
        $response,
        $plugin_id
      );
      $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
      $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], ['query' => ['method' => 'hd']])->toString()));
    }

    return $response;
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
    if (empty($address['country_id'])) {
      return [];
    }

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

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

    $values = $form_state->getValue($pane_form['#parents']);
    $address_values = $values['address']['shipping'];
    $email = $address_values['organization'];

    if ($violations = $address_book_manager->validateAddress($address_values)) {
      foreach ($violations as $field => $message) {
        $form_state->setErrorByName('billing_address][address][shipping][' . $field, $message);
      }
    }

    if ($form_state->getErrors()) {
      return;
    }

    if (user_load_by_mail($email)) {
      $login_link = Link::createFromRoute($this->t('please login'), 'acq_checkout.form', [
        'step' => 'login',
      ],
      [
        'query' => [
          'showlogin' => 1,
        ],
      ]);

      $form_state->setErrorByName('guest_delivery_home][address][shipping][organization', $this->t('You already have an account, @login_link.', [
        '@login_link' => $login_link->toString(),
      ]));

      return;
    }

    $address = $address_book_manager->getMagentoAddressFromAddressArray($address_values);

    $cart = $this->getCart();

    // We convert guest carts to customer cart or if user changes the email.
    if (!($cart->customerId()) || $cart->customerEmail() != $email) {
      // Get the customer id of Magento from this email.
      /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
      $api_wrapper = \Drupal::service('acq_commerce.api');

      /** @var \Drupal\acq_cart\CartSessionStorage $cart_storage */
      $cart_storage = \Drupal::service('acq_cart.cart_storage');

      try {
        $customer = [];
        $customer['firstname'] = $address['firstname'];
        $customer['lastname'] = $address['lastname'];
        $customer['email'] = $email;
        $customer = $api_wrapper->createCustomer($customer);

        $cart_storage->associateCart($customer['customer_id'], $email);
      }
      catch (\Exception $e) {
        if (acq_commerce_is_exception_api_down_exception($e)) {
          drupal_set_message($e->getMessage(), 'error');
          $form_state->setErrorByName('custom', $e->getMessage());
          return;
        }

        // Restore the cart to revert customer id and email in cart.
        $cart_storage->restoreCart($cart->id());

        // @TODO: Handle create customer errors here.
        // Probably just the email error.
        \Drupal::logger('alshaya_acm_checkout')->error('Error while creating customer for guest cart: @message', ['@message' => $e->getMessage()]);
        $error = $this->t('@title does not contain a valid email.', ['@title' => 'Email']);
        $form_state->setErrorByName('guest_delivery_home][address][shipping][organization', $error);
        return;
      }
    }

    if ($form_state->getErrors()) {
      return;
    }

    $this->getCheckoutHelper()->setCartShippingHistory('hd', $address);

    $shipping_method = NULL;

    if (isset($values['address']['shipping_methods'])) {
      $shipping_method = $values['address']['shipping_methods'];
      unset($values['address']['shipping_methods']);
    }

    if (empty($shipping_method) || $shipping_method == $checkout_options_manager->getClickandColectShippingMethod()) {
      return;
    }

    $cart->setShipping(_alshaya_acm_checkout_clean_address($address));

    $term = $checkout_options_manager->loadShippingMethod($shipping_method);

    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString(), []);

    // Clear the payment now.
    $this->getCheckoutHelper()->clearPayment();
  }

  /**
   * Wrapper function to add Delivery info into GTM.
   *
   * @param array $address
   *   Drupal address.
   */
  public static function exposeSelectedDeliveryAddressToGtm(array $address) {
    // Expose selected delivery address to GTM.
    if (\Drupal::moduleHandler()->moduleExists('alshaya_seo')) {
      /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
      $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

      $magento_address = $address_book_manager->getMagentoAddressFromAddressArray($address);

      datalayer_add([
        'deliveryArea' => $address_book_manager->getAddressShippingAreaValue($magento_address),
        'deliveryCity' => $address_book_manager->getAddressShippingAreaParentValue($address, $magento_address),
      ]);
    }
  }

}
