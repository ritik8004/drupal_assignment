(function ($, Drupal) {
  'use strict';

  var acq_cart_mini_cart_xhr;
  var acq_cart_mini_cart_timeout;

  Drupal.behaviors.acq_cart_js = {
    attach: function (context, settings) {
      // Do the processing only if we have mini cart available in DOM.
      if ($('body').find('#mini-cart-wrapper').length) {
        // Clear time out, behaviors can be called multiple times.
        if (acq_cart_mini_cart_timeout) {
          clearTimeout(acq_cart_mini_cart_timeout);
        }

        // Let other JS operations finish.
        acq_cart_mini_cart_timeout = setTimeout(Drupal.updateMiniCart, 50);
      }
    }
  };

  Drupal.updateMiniCart = function () {
    // Abort the AJAX operation if already started before starting new one.
    if (acq_cart_mini_cart_xhr && acq_cart_mini_cart_xhr.readyState !== 4) {
      acq_cart_mini_cart_xhr.abort();
    }

    acq_cart_mini_cart_xhr = $.ajax({
      url: Drupal.url('mini-cart'),
      success: function (result) {
        $('#mini-cart-wrapper').html(result);
      }
    });
  };

})(jQuery, Drupal);
