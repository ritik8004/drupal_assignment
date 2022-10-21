(function (Drupal) {
  Drupal.cartNotification = Drupal.cartNotification || {};

  Drupal.cartNotification.triggerNotification = function (data) {
    Drupal.cartNotification.spinner_stop();
    // Trigger cart drawer panel event when product added to cart.
    // Cart drawer panel will open as side drawer.
    var cartNotificationDrawer = new CustomEvent('showCartNotificationDrawer', {
      bubbles: true,
      detail: {
        productInfo: data,
      }
    });
    document.dispatchEvent(cartNotificationDrawer);
  }

})(Drupal);
