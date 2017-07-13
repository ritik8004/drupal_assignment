<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\Core\Url;

/**
 * Trait CheckoutDeliveryMethodTrait.
 *
 * @package Drupal\alshaya_acm_checkout
 *
 * @ingroup alshaya_acm_checkout
 */
trait CheckoutDeliveryMethodTrait {

  /**
   * Selected delivery method.
   *
   * @var string
   */
  protected static $deliveryMethodSelected;

  /**
   * Function to get selected delivery method code.
   *
   * @return mixed|string
   *   Selected delivery method code.
   *
   * @throws \Drupal\acq_commerce\Response\NeedsRedirectException
   */
  protected function getSelectedDeliveryMethod() {
    if (empty(self::$deliveryMethodSelected)) {
      // Check if we have selected method in query.
      $method = \Drupal::request()->get('method');

      $allowed_methods = ['hd', 'cc'];

      // We method is not allowed (someone trying to trick the system), we
      // set it to null and allow code below decide the active tab.
      if (!empty($method) && !in_array($method, $allowed_methods)) {
        $method = '';
      }

      if (empty($method)) {
        // We use the first method from allowed methods.
        $method = reset($allowed_methods);

        // Check once if we have a method available in cart.
        $cart = $this->getCart();
        if ($cart_method = $cart->getShippingMethodAsString()) {
          /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
          $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

          // Check if method available in cart is click and collect.
          $cart_method = $checkout_options_manager->getCleanShippingMethodCode($cart_method);
          if ($cart_method == $checkout_options_manager->getClickandColectShippingMethod()) {
            $method = 'cc';
          }
        }

        $redirect_url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery']);
        $redirect_url->setRouteParameter('method', $method);
        throw new NeedsRedirectException($redirect_url->toString());
      }

      self::$deliveryMethodSelected = $method;
    }

    return self::$deliveryMethodSelected;
  }

  /**
   * Function to get class for selected delivery method as string.
   *
   * @return string
   *   Return class for selected delivery method.
   */
  protected function getSelectedDeliveryMethodClass() {
    $method = $this->getSelectedDeliveryMethod();

    $classes = [
      'hd' => 'checkout-home-delivery',
      'cc' => 'checkout-click-collect',
    ];

    return $classes[$method];
  }

}
