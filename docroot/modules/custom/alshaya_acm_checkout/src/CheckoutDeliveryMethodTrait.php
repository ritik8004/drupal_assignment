<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\Core\Url;

/**
 * Trait Checkout Delivery Method Trait.
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
   * Selected delivery method in cart.
   *
   * @var string
   */
  protected static $deliveryMethodSelectedCart;

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

      self::$deliveryMethodSelectedCart = $cart_method;

      // If method is not allowed (someone trying to trick the system), redirect
      // to default or cart method.
      if ($method) {
        if (!in_array($method, $allowed_methods)) {
          $redirect_url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery']);

          if ($cart_method) {
            $redirect_url->setRouteParameter('method', $cart_method);
          }

          throw new NeedsRedirectException($redirect_url->toString());
        }
      }
      // If there is method available in cart, we use it.
      elseif (empty($method) && $cart_method) {
        $method = $cart_method;
      }
      // We use the first method from allowed methods as default.
      else {
        $method = reset($allowed_methods);
      }

      self::$deliveryMethodSelected = $method;
    }

    return self::$deliveryMethodSelected;
  }

  /**
   * Function to get selected delivery method code in cart.
   *
   * @return string
   *   Selected delivery method's code from cart.
   */
  public function getCartSelectedDeliveryMethod() {
    return self::$deliveryMethodSelectedCart;
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
      foreach ($items as $line_item) {
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

  /**
   * Check if user is changing mind by visiting another delivery method.
   *
   * @return bool
   *   TRUE if cart has delivery method and user visiting another method.
   */
  protected function isUserChangingHisMind() {
    $method = $this->getSelectedDeliveryMethod();
    $cart_method = $this->getCartSelectedDeliveryMethod();
    return $cart_method && $cart_method != $method;
  }

  /**
   * Wrapper function to clear shipping method info in Cart.
   */
  protected function clearShippingInfo() {
    /** @var \Drupal\alshaya_acm_checkout\CheckoutHelper $helper */
    $helper = \Drupal::service('alshaya_acm_checkout.checkout_helper');
    $helper->clearShippingInfo(self::$deliveryMethodSelectedCart);
    self::$deliveryMethodSelected = NULL;
    self::$deliveryMethodSelectedCart = NULL;
  }

  /**
   * Get checkout helper service object.
   *
   * @return \Drupal\alshaya_acm_checkout\CheckoutHelper
   *   Checkout Helper service object.
   */
  protected function getCheckoutHelper() {
    static $helper;

    if (empty($helper)) {
      /** @var \Drupal\alshaya_acm_checkout\CheckoutHelper $helper */
      $helper = \Drupal::service('alshaya_acm_checkout.checkout_helper');
    }

    return $helper;
  }

  /**
   * Get Address Book Manager helper service object.
   *
   * @return \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   *   Address Book Manager service object.
   */
  protected static function getAddressBookManager() {
    static $service;

    if (empty($service)) {
      /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $service */
      $service = \Drupal::service('alshaya_addressbook.manager');
    }

    return $service;
  }

  /**
   * Get address info from cart or history.
   *
   * @param string $method
   *   Method Code - hd/cc.
   *
   * @return array
   *   Address info - address + store info from extension.
   */
  protected function getAddressInfo($method = 'hd') {
    $response = [];

    $selected_method = $this->getCartSelectedDeliveryMethod();

    // Use from cart if shipping method set.
    if ($selected_method && $method === $selected_method) {
      /** @var \Drupal\acq_cart\Cart $cart */
      $cart = $this->getCart();

      /** @var \Drupal\alshaya_acm\CartHelper $cart_helper */
      $cart_helper = \Drupal::service('alshaya_acm.cart_helper');
      $response['address'] = $cart_helper->getShipping($cart);

      if ($selected_method === 'cc') {
        $response['store_code'] = $cart->getExtension('store_code');
        $response['click_and_collect_type'] = $cart->getExtension('click_and_collect_type');
      }
    }
    else {
      $response = $this->getCheckoutHelper()->getCartShippingHistory($method);
    }

    return $response;
  }

}
