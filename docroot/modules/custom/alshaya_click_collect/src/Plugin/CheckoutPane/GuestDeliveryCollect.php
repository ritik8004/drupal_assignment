<?php

namespace Drupal\alshaya_click_collect\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutDeliveryMethodTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
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
    return \Drupal::currentUser()->isAnonymous() && $this->getClickAndCollectAvailability();
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
    if (!$this->isVisible()) {
      return $pane_form;
    }

    if ($this->getSelectedDeliveryMethod() != 'cc') {
      return $pane_form;
    }

    $pane_form['#attributes']['class'][] = 'active--tab--content';

    $default_mobile = $shipping_type = $store_code = $selected_store_data = $store = '';
    $default_firstname = $default_lastname = $default_email = '';

    $cart = $this->getCart();
    $shipping = (array) $cart->getShipping();

    if ($form_values = $form_state->getValue($pane_form['#parents'])) {
      $store_code = $form_values['store_code'];
      $shipping_type = $form_values['shipping_type'];
      $default_mobile = $form_values['cc_mobile'];
    }
    elseif ($cart->getExtension('store_code') && $shipping && !empty($shipping['telephone'])) {
      // Check if value available in shipping address.
      $store_code = $cart->getExtension('store_code');
      $shipping_type = $cart->getExtension('click_and_collect_type');
      $default_mobile = $shipping['telephone'];
    }

    if ($store_code && $shipping_type) {
      // Not injected here to avoid module dependency.
      // Get store info.
      $store_utility = \Drupal::service('alshaya_stores_finder.utility');
      $store = $store_utility->getStoreExtraData(['code' => $store_code]);

      if (!empty($store)) {
        if ($shipping_type == 'reserve_and_collect') {
          $store['delivery_time'] = \Drupal::config('alshaya_click_collect.settings')->get('click_collect_rnc');
        }

        $selected_store = [
          '#theme' => 'click_collect_selected_store',
          '#store' => $store,
        ];
        $selected_store_data = render($selected_store);
      }
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
      '#title' => $this->t('find your closest collection point'),
      '#prefix' => '<div class="label-store-location">' . $this->t('find your closest collection point') . '</div>',
      '#placeholder' => t('Enter a location'),
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

    $pane_form['selected_store']['elements'] = [
      '#type' => 'container',
      '#tree' => FALSE,
      '#id' => 'selected-store-elements-wrapper',
    ];

    $pane_form['selected_store']['elements']['customer_help'] = [
      '#markup' => '<div class="cc-help-text cc-customer-help-text"><p>' . $this->t("Please provide your contact details") . '</p>' . $this->t("We’ll be using this information to keep in touch with you") . '</div>',
    ];

    // First/Last/Email/Mobile have cc_ prefix to ensure validations work fine
    // and don't conflict with address form fields.
    // @TODO: Add input validation. Check in addressbook (Rohit/Mitesh).
    $pane_form['selected_store']['elements']['cc_firstname'] = [
      '#type' => 'textfield',
      '#title' => t('First Name'),
      '#required' => TRUE,
      '#default_value' => $default_firstname,
    ];

    // @TODO: Add input validation. Check in addressbook (Rohit/Mitesh).
    $pane_form['selected_store']['elements']['cc_lastname'] = [
      '#type' => 'textfield',
      '#title' => t('Last Name'),
      '#required' => TRUE,
      '#default_value' => $default_lastname,
    ];

    $pane_form['selected_store']['elements']['cc_email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
      '#default_value' => $default_email,
    ];

    $pane_form['selected_store']['elements']['mobile_help'] = [
      '#markup' => '<div class="cc-help-text cc-mobile-help-text"><p>' . $this->t("Please provide the mobile number of the person collecting the order") . '</p>' . $this->t("We'll send you a text message when the order is ready to collect") . '</div>',
    ];

    $pane_form['selected_store']['elements']['cc_mobile_number'] = [
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
            'zoom' => 11,
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
          'selected_store_obj' => $store,
          // Default site country to limit autocomplete result.
          'country' => _alshaya_custom_get_site_level_country_code(),
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
        'wrapper' => 'selected-store-elements-wrapper',
      ],
      // This is required for limit_validation_errors to work.
      '#submit' => [],
      '#limit_validation_errors' => [
        ['guest_delivery_collect'],
        ['selected_store'],
        ['cc_mobile_number'],
        ['cc_firstname'],
        ['cc_lastname'],
        ['cc_email'],
      ],
    ];

    $complete_form['actions']['next']['#attributes']['class'][] = 'hidden-important';

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

    if ($user = user_load_by_mail($values['cc_email'])) {
      $form_state->setErrorByName('cc_email', $this->t('You already have an account, @login_link.', [
        '@login_link' => Link::createFromRoute($this->t('please login'), 'acq_checkout.form', ['step' => 'login'])->toString(),
      ]));
      return;
    }

    $cart = $this->getCart();

    // We convert guest carts to customer cart or if user changes the email.
    if (!($cart->customerId()) || $cart->customerEmail() != $values['cc_email']) {
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
        $error = $this->t('@title does not contain a valid email.', ['@title' => 'Email']);
        $form_state->setErrorByName('cc_email', $error);
        return;
      }

      /** @var \Drupal\acq_cart\CartSessionStorage $cart_storage */
      $cart_storage = \Drupal::service('acq_cart.cart_storage');
      $cart_storage->associateCart($customer['customer_id'], $values['cc_email']);
    }

    $extension = [];

    $extension['store_code'] = $values['store_code'];
    $extension['click_and_collect_type'] = $values['shipping_type'];

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    $term = $checkout_options_manager->getClickandColectShippingMethodTerm();

    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString(), $extension);

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');
    $address = $address_book_manager->getAddressStructureWithEmptyValues();

    // Adding first and last name from custom info.
    $address['firstname'] = $values['cc_firstname'];
    $address['lastname'] = $values['cc_lastname'];

    $address['telephone'] = _alshaya_acm_checkout_clean_address_phone($values['cc_mobile_number']);

    /** @var \Drupal\alshaya_stores_finder\StoresFinderUtility $store_utility */
    $store_utility = \Drupal::service('alshaya_stores_finder.utility');
    $store_node = $store_utility->getTranslatedStoreFromCode($values['store_code']);

    $address['extension']['address_area_segment'] = $store_node->get('field_store_area')->getString();

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
      return $form['guest_delivery_collect']['selected_store']['elements'];
    }

    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString()));
    return $response;
  }

}
