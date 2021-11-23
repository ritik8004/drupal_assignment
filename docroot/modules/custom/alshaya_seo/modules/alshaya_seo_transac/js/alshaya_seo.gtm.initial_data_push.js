/**
 * @file
 * Event Listener to alter datalayer.
 */

(function ($, Drupal) {
  document.addEventListener('alterInitialDataLayerData', (e) => {
    let cartId = localStorage.getItem('cart_id');
    if (cartId) {
      e.detail.data().cart_id = cartId;
    }
  });
})(jQuery, Drupal);
