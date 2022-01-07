/**
 * @file
 * Event Listener to alter datalayer.
 */

(function () {
  document.addEventListener('alterInitialDataLayerData', (e) => {

    // @todo Below code is duplicate from window.commerceBackend.getCartId().
    // which can be replaced with the function While refactoring.

    // Fetch cart id for anonymous user.
    let cartId = localStorage.getItem('cart_id');
    if (typeof cartId === 'undefined' || cartId === null) {
      // Fetch cart id from cart data for authenticated users.
      let data = JSON.parse(localStorage.getItem('cart_data'));
      if (typeof data !== 'undefined'
        && typeof data.cart !== 'undefined'
        && typeof data.cart.cart_id !== 'undefined'
        && data.cart.cart_id !== null
      ) {
        cartId = data.cart.cart_id;
      }
    }
    if (typeof cartId === 'string' || typeof cartId === 'number') {
      e.detail.data().cart_id = cartId;
    }
  });
})();
