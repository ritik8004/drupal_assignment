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

}
