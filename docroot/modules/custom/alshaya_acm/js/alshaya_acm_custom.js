(function ($) {
  Drupal.behaviors.alshaya_acm_js = {
    attach: function (context, settings) {
      $('.acq-cart-summary').once('bind-events').each(function () {
        $('.content-items', $(this)).slideUp();

        $('.content-head', $(this)).on('click', function () {
          $(this).parent().toggleClass('active--accordion');
          $(this).next().slideToggle();
        });
      });

      // Hide apply coupon button on page load.
      var applyCoupon = $('#apply_coupon');

      $('.customer-cart-form', context).once('bind-events').each(function () {
        $('#apply_coupon', $(this)).on('click', function () {
          $('[data-drupal-selector="edit-update"]').trigger('click');
        });

        $('#checkout-top', $(this)).on('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          $('[data-drupal-selector="edit-checkout"]').trigger('click');
        });
      });

      $('#apply_coupon').once('load').each(function () {
        // Hide the apply button on page load or after AJAX call replacing form.
        $(this).hide();

        // Also store current value in data attributes to use for validation later.
        $(this).data('applied-coupon', $('[data-drupal-selector="edit-coupon"]').val().trim());
      });

      $('[data-drupal-selector="edit-coupon"]').bind('bind-events').on('keyup', function () {
        var applied_coupon = $('#apply_coupon').data('applied-coupon');
        var new_value = $(this).val().trim();

        // If new value is not equal to stored value, we show the apply button.
        if (new_value !== applied_coupon) {
          $('#apply_coupon').slideDown();
        }
        else {
          $('#apply_coupon').slideUp();
        }
      });
    }
  };

  $.fn.updateOutOfStockDom = function (message) {
    if ($('#out-of-stock-message').length) {
      $('#out-of-stock-message').html(message);
    }
    else {
      var error_div = '<div id="out-of-stock-message">' + message + '</div>';
      $('#table-cart-items').before(error_div);
    }
  };

  $.fn.removeCartItem = function (sku) {
    var removedItem = $('#edit-cart [gtm-product-sku="' + sku + '"]');
    removedItem.trigger('cart-item-removed');
    window.location.reload();
  }

})(jQuery);
