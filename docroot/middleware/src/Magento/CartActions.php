<?php

namespace AlshayaMiddleware\Magento;

/**
 * Class CartActions.
 *
 * Class contains al the actions performed on the cart.
 */
final class CartActions {

  /**
   * Action for creating new cart.
   */
  const CART_CREATE_NEW = 'create cart';

  /**
   * Action used for adding an item in cart.
   */
  const CART_ADD_ITEM = 'add item';

  /**
   * Action used for updating quantity of an item in cart.
   */
  const CART_UPDATE_ITEM = 'update item';

  /**
   * Action used for removing an item in cart.
   */
  const CART_REMOVE_ITEM = 'remove item';

  /**
   * Action used for applying coupon on cart.
   */
  const CART_APPLY_COUPON = 'apply coupon';

  /**
   * Action used for removing coupon from cart.
   */
  const CART_REMOVE_COUPON = 'remove coupon';

}
