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
   * Function to check if parameter in query is available of not.
   *
   * @return bool
   *   True if method available in request param and has value.
   */
  protected function isMethodParamAvailable() {
    return (bool) \Drupal::request()->get('method');
  }

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

      // Check once if we have a method available in cart.
      $cart_method = '';
      $cart = $this->getCart();
      if ($cart_method_code = $cart->getShippingMethodAsString()) {
        $cart_method = 'hd';

        /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
        $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

        // Check if method available in cart is click and collect.
        $cart_method_code = $checkout_options_manager->getCleanShippingMethodCode($cart_method_code);
        if ($cart_method_code == $checkout_options_manager->getClickandColectShippingMethod()) {
          $cart_method = 'cc';
        }
      }

      // We method is not allowed (someone trying to trick the system), redirect
      // to default or cart method.
      if ($method && !in_array($method, $allowed_methods)) {
        $redirect_url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery']);

        if ($cart_method) {
          $redirect_url->setRouteParameter('method', $cart_method);
        }

        throw new NeedsRedirectException($redirect_url->toString());
      }

      if (empty($method) && $cart_method) {
        $redirect_url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery']);
        $redirect_url->setRouteParameter('method', $cart_method);
        throw new NeedsRedirectException($redirect_url->toString());
      }

      if (empty($method)) {
        // We use the first method from allowed methods as default.
        $method = reset($allowed_methods);
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

  /**
   * Check if products in cart are available for click and collect.
   *
   * @return int
   *   0 if not available, 1 if click and collect available.
   */
  protected function getClickAndCollectAvailability() {
    $status = &drupal_static(__FUNCTION__);
    if (isset($status)) {
      return $status;
    }
    // Include the utility file.
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    // Get the cart items.
    $items = $this->getCart()->items();
    // Click and collect status.
    $status = 1;

    if (!empty($items)) {
      // Loop through each cart items to get the status of click and collect.
      foreach ($items as $index => $line_item) {
        // Check the status of click and collect for the given sku.
        if (!alshaya_acm_product_available_click_collect($line_item['sku'])) {
          // If click and collect is not available for a product.
          // Set status 0 and break the loop.
          $status = 0;
          break;
        }
      }
    }

    return $status;
  }

}
