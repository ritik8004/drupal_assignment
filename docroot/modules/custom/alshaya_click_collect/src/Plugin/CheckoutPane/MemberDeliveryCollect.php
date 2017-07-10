<?php

namespace Drupal\alshaya_click_collect\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\geolocation\GoogleMapsDisplayTrait;
use Drupal\user\Entity\User;

/**
 * Provides the delivery CnC pane for members.
 *
 * @ACQCheckoutPane(
 *   id = "member_delivery_collect",
 *   label = @Translation("Click and Collect"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class MemberDeliveryCollect extends CheckoutPaneBase implements CheckoutPaneInterface {
  // Add trait to get map url from getGoogleMapsApiUrl().
  use GoogleMapsDisplayTrait;

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAuthenticated();
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
    if (\Drupal::currentUser()->isAnonymous()) {
      return $pane_form;
    }

    $cart = $this->getCart();

    $pane_form['store_finder'] = [
      '#type' => 'container',
      '#title' => t('store finder'),
      '#tree' => FALSE,
      '#id' => 'store-finder-wrapper',
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
      '#attributes' => ['style' => 'display:none;'],
    ];

    $pane_form['selected_store']['content'] = [
      '#markup' => '<div id="selected-store-content" class="selected-store-content"></div>',
    ];

    $default_mobile = '';

    $shipping = (array) $cart->getShipping();
    if ($cart->getExtension('store_code') && $shipping) {
      // Check if value available in shipping address.
      $default_mobile = $shipping['telephone'];
    }
    else {
      // Check once in customer profile.
      $account = User::load(\Drupal::currentUser()->id());
      if ($account_phone = $account->get('field_mobile_number')->getValue()) {
        $default_mobile = $account_phone[0]['value'];
      }
    }

    $pane_form['selected_store']['mobile_help'] = [
      '#markup' => '<div class="cc-help-text cc-mobile-help-text">' . $this->t("<p>Please provide the mobile number of the person collecting the order.</p>We'll send you a text message when the order is ready to collect") . '</div>',
    ];

    // @TODO: Verify mobile validation. Check in addressbook (Rohit/Mitesh).
    $pane_form['selected_store']['mobile_number'] = [
      '#type' => 'mobile_number',
      '#title' => $this->t('Mobile Number'),
      '#verify' => 0,
      '#tfa' => 0,
      '#required' => TRUE,
      '#default_value' => ['value' => $default_mobile],
    ];

    $pane_form['selected_store']['store_code'] = [
      '#type' => 'hidden',
      '#default_value' => $cart->getExtension('store_code'),
    ];

    $pane_form['selected_store']['shipping_type'] = [
      '#type' => 'hidden',
      '#default_value' => $cart->getExtension('click_and_collect_type'),
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
        'alshaya_click_collect' => ['cart_id' => $cart->id()],
      ],
      'library' => [
        'alshaya_click_collect/click-and-collect.checkout',
      ],
    ];

    $complete_form['actions']['ccnext'] = $complete_form['actions']['next'];
    $complete_form['actions']['ccnext']['#limit_validation_errors'] = [['mobile_number']];
    $complete_form['actions']['ccnext']['#attributes']['class'][] = 'cc-action';
    $complete_form['actions']['ccnext']['#ajax'] = [
      'callback' => [$this, 'submitMemberDeliveryCollect'],
      'wrapper' => 'selected-store-wrapper',
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

    $extension = [];

    $extension['store_code'] = $values['store_code'];
    $extension['click_and_collect_type'] = $values['shipping_type'];

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    $term = $checkout_options_manager->getClickandColectShippingMethodTerm();

    $cart = $this->getCart();
    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString(), $extension);

    $address = [
      'country_id' => _alshaya_custom_get_site_level_country_code(),
      'telephone' => _alshaya_acm_checkout_clean_address_phone($values['mobile_number']),
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
      return $form['member_delivery_collect']['selected_store'];
    }

    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString()));
    return $response;
  }

}
