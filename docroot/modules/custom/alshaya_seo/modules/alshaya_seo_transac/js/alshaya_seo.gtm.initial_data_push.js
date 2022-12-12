/**
 * @file
 * Event Listener to alter datalayer.
 */

(function () {
  document.addEventListener('dataLayerContentAlter', (e) => {
    // @todo Below code is duplicate from window.commerceBackend.getCartId().
    // which can be replaced with the function While refactoring.

    // Fetch cart id for anonymous user.
    let cartId = Drupal.getItemFromLocalStorage('cart_id');
    if (cartId != 'NA' && Boolean(window.drupalSettings.userDetails.customerId))  {
      let userCartId = Drupal.getItemFromLocalStorage('guestCartForMerge');
      if (Drupal.hasValue(userCartId) && Drupal.hasValue(userCartId.active_quote)) {
        cartId = userCartId.active_quote;
      }
    }
    if (typeof cartId === 'undefined' || cartId === null || cartId === 'NA') {
      // Fetch cart id from cart data for authenticated users.
      let data = Drupal.getItemFromLocalStorage('cart_data');
      if (typeof data !== 'undefined'
        && data !== null
        && typeof data.cart !== 'undefined'
        && typeof data.cart.cart_id !== 'undefined'
        && data.cart.cart_id !== null
      ) {
        cartId = data.cart.cart_id;
      }
      else {
        // Fetch cart id from user_cart_id for authenticated users if available.
        let userCartId = Drupal.getItemFromLocalStorage('user_cart_id');
        if (typeof userCartId !== 'undefined') {
          cartId = userCartId;
        }
      }
    }
    if (typeof cartId === 'string' || typeof cartId === 'number') {
      e.detail.data().cart_id = cartId;
    }
  });
})();
