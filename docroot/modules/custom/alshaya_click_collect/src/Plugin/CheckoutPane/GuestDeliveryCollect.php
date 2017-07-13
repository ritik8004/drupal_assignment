<?php

namespace Drupal\alshaya_click_collect\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutDeliveryMethodTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\geolocation\GoogleMapsDisplayTrait;

/**
 * Provides the delivery CnC pane for guests.
 *
 * @ACQCheckoutPane(
 *   id = "guest_delivery_collect",
 *   label = @Translation("<h2>Click & Collect</h2><p>Collect your order in-store</p>"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class GuestDeliveryCollect extends CheckoutPaneBase implements CheckoutPaneInterface {
  // Add trait to get map url from getGoogleMapsApiUrl().
  use GoogleMapsDisplayTrait;

  // Add trait to get selected delivery method tab.
  use CheckoutDeliveryMethodTrait;

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
      'weight' => 2,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if (\Drupal::currentUser()->isAuthenticated()) {
      return $pane_form;
    }

    if ($this->getSelectedDeliveryMethod() != 'cc') {
      return $pane_form;
    }

    $default_mobile = $shipping_type = $store_code = $selected_store_data = '';
    $default_firstname = $default_lastname = $default_email = '';

    $cart = $this->getCart();
    $shipping = (array) $cart->getShipping();

    $store_code = '';
    $shipping_type = '';

    if ($form_values = $form_state->getValue($pane_form['#parents'])) {
      $store_code = $form_values['cc_firstname'];
      $shipping_type = $form_values['cc_lastname'];
      $default_mobile = $form_values['cc_mobile'];
    }
    elseif ($cart->getExtension('store_code') && $shipping && !empty($shipping['telephone'])) {
      // Check if value available in shipping address.
      $default_mobile = $shipping['telephone'];
      $store_code = $cart->getExtension('store_code');
      $shipping_type = $cart->getExtension('click_and_collect_type');
    }

    if ($store_code && $shipping_type) {
      // Not injected here to avoid module dependency.
      // Get store info.
      $store_utility = \Drupal::service('alshaya_stores_finder.utility');
      $store = $store_utility->getStoreExtraData(['code' => $store_code]);
      $selected_store = [
        '#theme' => 'click_collect_selected_store',
        '#store' => $store,
      ];

      $selected_store_data = render($selected_store);
    }

    // Get Customer info.
    /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
    $api_wrapper = \Drupal::service('acq_commerce.api');
    if (!empty($form_values)) {
      $default_firstname = $form_values['cc_firstname'];
      $default_lastname = $form_values['cc_lastname'];
      $default_email = $form_values['cc_email'];
    }
    elseif ($customer_email = $cart->customerEmail()) {
      if ($customer = $api_wrapper->getCustomer($cart->customerEmail())) {
        $default_firstname = $customer['firstname'];
        $default_lastname = $customer['lastname'];
        $default_email = $cart->customerEmail();
      }
    }

    $pane_form['store_finder'] = [
      '#type' => 'container',
      '#title' => t('store finder'),
      '#tree' => FALSE,
      '#id' => 'store-finder-wrapper',
      '#attributes' => ($store_code) ? ['style' => 'display:none;'] : [],
    ];

    $pane_form['store_finder']['store_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Find your closest collection point'),
      '#prefix' => '<div class="label-store-location">' . $this->t('Find your closest collection point') . '</div>',
      '#attributes' => [
        'class' => ['store-location-input'],
      ],
    ];

    $pane_form['store_finder']['toggle_list_view'] = [
      '#markup' => '<a href="#" class="stores-list-view active">' . $this->t('List view') . '</a>',
    ];

    $pane_form['store_finder']['toggle_map_view'] = [
      '#markup' => '<a href="#" class="stores-map-view">' . $this->t('Map view') . '</a>',
    ];

    $pane_form['store_finder']['list_view'] = [
      '#type' => 'container',
      '#id' => 'click-and-collect-list-view',
    ];

    $pane_form['store_finder']['map_view'] = [
      '#type' => 'container',
      '#id' => 'click-and-collect-map-view',
    ];

    $pane_form['store_finder']['map_view']['content'] = [
      '#markup' => '<div class="geolocation-common-map-container"></div>',
    ];

    $pane_form['store_finder']['map_view']['locations'] = [
      '#markup' => '<div class="geolocation-common-map-locations" style="display: none;"></div>',
    ];

    $pane_form['selected_store'] = [
      '#type' => 'container',
      '#title' => t('selected store'),
      '#tree' => FALSE,
      '#id' => 'selected-store-wrapper',
      '#attributes' => ($store_code) ? [] : ['style' => 'display:none;'],
    ];

    $pane_form['selected_store']['content'] = [
      '#markup' => '<div id="selected-store-content" class="selected-store-content">' . $selected_store_data . '</div>',
    ];

    $pane_form['selected_store']['customer_help'] = [
      '#markup' => '<div class="cc-help-text cc-customer-help-text"><p>' . $this->t("Please provide your contact details") . '</p>' . $this->t("We’ll be using this information to keep in touch with you") . '</div>',
    ];

    // First/Last/Email/Mobile have cc_ prefix to ensure validations work fine
    // and don't conflict with address form fields.
    // @TODO: Add input validation. Check in addressbook (Rohit/Mitesh).
    $pane_form['selected_store']['cc_firstname'] = [
      '#type' => 'textfield',
      '#title' => t('First Name'),
      '#required' => TRUE,
      '#default_value' => $default_firstname,
    ];

    // @TODO: Add input validation. Check in addressbook (Rohit/Mitesh).
    $pane_form['selected_store']['cc_lastname'] = [
      '#type' => 'textfield',
      '#title' => t('Last Name'),
      '#required' => TRUE,
      '#default_value' => $default_lastname,
    ];

    $pane_form['selected_store']['cc_email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
      '#default_value' => $default_email,
    ];

    $pane_form['selected_store']['mobile_help'] = [
      '#markup' => '<div class="cc-help-text cc-mobile-help-text"><p>' . $this->t("Please provide the mobile number of the person collecting the order") . '</p>' . $this->t("We'll send you a text message when the order is ready to collect") . '</div>',
    ];

    $pane_form['selected_store']['cc_mobile_number'] = [
      '#type' => 'mobile_number',
      '#title' => t('Mobile Number'),
      '#verify' => 0,
      '#tfa' => 0,
      '#required' => TRUE,
      '#default_value' => ['value' => $default_mobile],
    ];

    $pane_form['selected_store']['store_code'] = [
      '#type' => 'hidden',
      '#default_value' => $store_code,
    ];

    $pane_form['selected_store']['shipping_type'] = [
      '#type' => 'hidden',
      '#default_value' => $shipping_type,
    ];

    $pane_form['#attached'] = [
      'drupalSettings' => [
        'geolocation' => [
          'google_map_url' => $this->getGoogleMapsApiUrl(),
          'google_map_settings' => [
            'type' => static::$ROADMAP,
            'zoom' => 10,
            'minZoom' => 0,
            'maxZoom' => 18,
            'rotateControl' => 0,
            'mapTypeControl' => 1,
            'streetViewControl' => 1,
            'zoomControl' => 1,
            'fullscreenControl' => 0,
            'scrollwheel' => 1,
            'disableDoubleClickZoom' => 0,
            'draggable' => 1,
            'height' => '815px',
            'width' => '100%',
            'info_auto_display' => 0,
            'marker_icon_path' => '/themes/custom/alshaya_white_label/imgs/icons/google-map-marker.svg',
            'disableAutoPan' => 1,
            'style' => '',
            'preferScrollingToZooming' => 0,
            'gestureHandling' => 'auto',
          ],
        ],
        'alshaya_click_collect' => [
          'cart_id' => $cart->id(),
          'selected_store' => ($store_code) ? TRUE : FALSE,
        ],
      ],
      'library' => [
        'alshaya_click_collect/click-and-collect.checkout',
      ],
    ];

    $complete_form['actions']['ccnext'] = [
      '#name' => 'ccnext',
      '#type' => 'submit',
      // Drupal processes limit_validations_errors based on value of the button
      // and we want to have same button "proceed to payment" for both the tabs
      // but still want different validations to work on both.
      // Space here is added just to keep them separate for drupal but still
      // have same text in frontend.
      '#value' => $complete_form['actions']['next']['#value'] . ' ',
      '#attributes' => [
        'class' => ['cc-action'],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitMemberDeliveryCollect'],
        'wrapper' => 'selected-store-wrapper',
      ],
      // This is required for limit_validation_errors to work.
      '#submit' => [],
      '#limit_validation_errors' => [
        ['selected_store'],
        ['cc_mobile_number'],
        ['cc_firstname'],
        ['cc_lastname'],
        ['cc_email'],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getValue('selected_tab') != 'checkout-click-collect') {
      return;
    }

    if ($form_state->getErrors()) {
      return;
    }

    $values = $form_state->getValues($pane_form['#parents']);

    $cart = $this->getCart();

    // We are only looking to convert guest carts.
    if (!($cart->customerId())) {
      // Get the customer id of Magento from this email.
      /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
      $api_wrapper = \Drupal::service('acq_commerce.api');

      try {
        $customer = $api_wrapper->createCustomer($values['cc_firstname'], $values['cc_lastname'], $values['cc_email']);
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
        $customer_cart['customer_email'] = $values['cc_email'];
      }

      $cart->convertToCustomerCart($customer_cart);
      \Drupal::service('acq_cart.cart_storage')->addCart($cart);
    }

    $extension = [];

    $extension['store_code'] = $values['store_code'];
    $extension['click_and_collect_type'] = $values['shipping_type'];

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    $term = $checkout_options_manager->getClickandColectShippingMethodTerm();

    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString(), $extension);

    $address = [
      'country_id' => _alshaya_custom_get_site_level_country_code(),
      'telephone' => _alshaya_acm_checkout_clean_address_phone($values['cc_mobile_number']),
    ];

    $cart->setShipping($address);
  }

  /**
   * Ajax callback to submit member delivery collect.
   *
   * @param mixed|array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response to reload page on successfully adding new address.
   */
  public function submitMemberDeliveryCollect($form, FormStateInterface $form_state) {
    if ($form_state->getErrors()) {
      return $form['guest_delivery_collect']['selected_store'];
    }

    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString()));
    return $response;
  }

}
