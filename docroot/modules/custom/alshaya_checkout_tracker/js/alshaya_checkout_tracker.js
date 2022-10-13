/**
 * @file
 * Removes progress tracker for empty cart.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.checkouttracker = {
    attach: function (context, settings) {
      const cartData = Drupal.alshayaSpc.getCartData();
      if (drupalSettings.path.currentPath === 'cart' && !(cartData && Drupal.hasValue(cartData.items))) {
        $('#block-checkouttrackerblock').addClass('hide-checkout-tracker');
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
