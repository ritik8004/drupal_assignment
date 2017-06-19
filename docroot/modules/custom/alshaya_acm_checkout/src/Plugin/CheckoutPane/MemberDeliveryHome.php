<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides the delivery home pane for members.
 *
 * @ACQCheckoutPane(
 *   id = "member_delivery_home",
 *   label = @Translation("Home delivery"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class MemberDeliveryHome extends CheckoutPaneBase implements CheckoutPaneInterface {

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
      'weight' => 1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if (\Drupal::currentUser()->isAnonymous()) {
      return $pane_form;
    }

    $pane_form['#suffix'] = '<div class="fieldsets-separator">' . $this->t('OR') . '</div>';
    $pane_form['guest_delivery_home']['title'] = [
      '#markup' => '<div class="title">' . $this->t('delivery information') . '</div>',
    ];

    $cart = $this->getCart();
    $address = $cart->getShipping();

    // This class is required to make theme work properly.
    $pane_form['#attributes']['class'] = 'c-address-book';

    $pane_form['address_book_wrapper'] = [
      '#type' => 'item',
      '#markup' => '<div id="address-book-form-wrapper"></div>',
    ];

    $pane_form['header'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['addresses_header'],
      ],
    ];

    $pane_form['header']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $this->t('choose delivery address') . '</h2>',
    ];

    $add_profile_route_params = [
      'user' => \Drupal::currentUser()->id(),
      'profile_type' => 'address_book',
      'js' => 'nojs',
    ];

    $add_profile_route_options = [
      'attributes' => [
        'class' => ['use-ajax'],
        'rel' => 'address-book-form-wrapper',
      ],
      'query' => [
        'from' => 'checkout',
      ],
    ];

    $pane_form['header']['add_profile'] = Link::createFromRoute(
      $this->t('add new address'),
      'alshaya_addressbook.add_address_ajax',
      $add_profile_route_params,
      $add_profile_route_options)->toRenderable();

    $pane_form['addresses'] = [
      '#type' => 'view',
      '#name' => 'address_book',
      '#display_id' => 'address_book',
      '#embed' => TRUE,
      '#title' => '',
      '#pre_render' => [
        ['\Drupal\views\Element\View', 'preRenderViewElement'],
      ],
    ];

    $shipping_methods = GuestDeliveryHome::generateShippingEstimates($address);
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

    unset($complete_form['actions']['get_shipping_methods']);

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);

    $shipping_method = isset($values['address']['shipping_methods']) ? $values['address']['shipping_methods'] : NULL;

    if (empty($shipping_method)) {
      return;
    }

    $cart = $this->getCart();

    $term = alshaya_acm_checkout_load_shipping_method($shipping_method);
    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString());
  }

}
