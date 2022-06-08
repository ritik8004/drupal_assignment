(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaRcsPdpLoaded = {
    attach: function (context, settings) {
      if (!$('.add_to_cart_form').hasClass('rcs-loaded')) {
        return;
      }
      // Render wishlist button once add to cart is loaded.
      $('.add_to_cart_form').once('add-to-cart-loaded').each(function () {
        RcsEventManager.fire('alshayaAddToCartLoaded');
      });
    }
  }
})(jQuery, Drupal);
