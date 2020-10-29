<?php

namespace App\Helper;

/**
 * Class Cart Info Helper.
 *
 * @package App\Helper
 */
class CartInfoHelper {

  /**
   * Helper function to check if cart expired.
   *
   * @param array $cart
   *   Cart data.
   * @param array $checkout_settings
   *   Checkout settings.
   *
   * @return bool
   *   If cart is expired.
   */
  public static function isCartExpired(array $cart, array $checkout_settings) {
    // Check if last update of our cart is more recent than X minutes.
    $expiration_time = $checkout_settings['purchase_expiration_time'];
    $cart_last_updated = strtotime($cart['cart']['updated_at']);
    $current_time = strtotime(date('Y-m-d H:i:s'));
    $time_difference = round(abs($current_time - $cart_last_updated) / 60, 2);

    // If time difference more then call getCart to get fresh data.
    if ($time_difference > $expiration_time) {
      return TRUE;
    }

    return FALSE;
  }

}
