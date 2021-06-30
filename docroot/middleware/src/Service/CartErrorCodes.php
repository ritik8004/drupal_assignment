<?php

namespace App\Service;

/**
 * Class CartErrorCodes.
 *
 * Class contains error codes we send to FE.
 */
final class CartErrorCodes {

  /**
   * Error code when cart has OOS item.
   */
  const CART_HAS_OOS_ITEM = 506;

  /**
   * Error code on order placement.
   */
  const CART_ORDER_PLACEMENT_ERROR = 505;

  /**
   * Error code on checkout.
   *
   * This is triggered in Magento response when there is stock quantity mismatch
   *  between Magento and OMS during checkout.
   */
  const CART_CHECKOUT_QUANTITY_MISMATCH = 9010;

}
