(function ($, Drupal, document) {
  'use strict';

  Drupal.behaviors.alshayaSpcCartValidate = {
    attach: function (context, settings) {

      // Redirect user back to cart page, if user
      var cart_data = localStorage.getItem('cart_data');
      if (cart_data) {
        cart_data = JSON.parse(cart_data);
        cart_data = cart_data.cart;
        if (cart_data.cart_id === null || typeof cart_data.cart.cart_id !== "number") {
          var progress_element = $('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
          $('body').after(progress_element);
          window.location.pathname = '/' + drupalSettings.path.currentLanguage + '/cart';
        }
      }
    }
  };

})(jQuery, Drupal, document);
