/**
 * @file
 * Event Listener to alter datalayer.
 */

(function () {
  document.addEventListener('alterInitialDataLayerData', (e) => {
    let cartId = Drupal.getItemFromLocalStorage('cart_id');
    if (cartId) {
      e.detail.data().cart_id = cartId;
    }
  });
})();
