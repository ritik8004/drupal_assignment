(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.acq_cart_js = {
    attach: function (context, settings) {
      if (context === document) {
        $.ajax({
          url: Drupal.url('mini-cart'),
          success: function (result) {
            $('#mini-cart-wrapper').html(result);
          }
        });
      }
    }
  };

})(jQuery, Drupal);
