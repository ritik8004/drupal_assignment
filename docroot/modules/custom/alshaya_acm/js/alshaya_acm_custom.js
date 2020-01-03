(function ($) {
  Drupal.behaviors.alshaya_acm_js = {
    attach: function (context, settings) {
      $('.acq-cart-summary').once('bind-events').each(function () {
        $('.content-head', $(this)).on('click', function () {
          $(this).parent().toggleClass('active--accordion');
          $(this).next().slideToggle();

          if (typeof Drupal.blazyRevalidate !== 'undefined') {
            Drupal.blazyRevalidate();
          }
        });
      });

      $('.coupon-code-wrapper').once('coupon-code').on('accordion:initialized', function() {
        // Activate the accordion in case we have a coupon code applied to the
        // cart.
        if (($('input.cancel-promocode').length > 0) &&
          ($('input.cancel-promocode').val() !== '')) {
          $('.coupon-code-wrapper').accordion('option', 'active', 0 );
        }
      });

      // Hide apply coupon button on page load.
      $('.customer-cart-form', context).once('bind-events').each(function () {
        $('#coupon-button', $(this)).on('click', function (e) {
          if ($(this).hasClass('remove')) {
            e.preventDefault();
            e.stopPropagation();
            $('input.cancel-promocode').val('');
          }
          $('[data-drupal-selector="edit-update"]').trigger('click');
        });

        $('#checkout-top', $(this)).on('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          $('[data-drupal-selector="edit-checkout"]').trigger('click');
        });
      });

      // Trigger coupon apply button when clicking on coupon code in promo label.
      $('.sku-promotions .coupon-code, .free-gifts-wrapper .coupon-code').click(function() {
        $('#edit-coupon').val($(this).text());
        $('#coupon-button').click();
      });

      $('#coupon-button.add').once('load').each(function () {
        // Hide the apply button on page load or after AJAX call replacing form.
        $(this).hide();

        // Also store current value in data attributes to use for validation later.
        $(this).data('applied-coupon', $('[data-drupal-selector="edit-coupon"]').val().trim());
      });

      $('[data-drupal-selector="edit-coupon"]').on('bind-events').on('keyup', function () {
        var applied_coupon = $('#coupon-button').data('applied-coupon');
        var new_value = $(this).val().trim();

        // If new value is not equal to stored value, we show the apply button.
        if (new_value !== applied_coupon) {
          $('#coupon-button').slideDown();
        }
        else {
          $('#coupon-button').slideUp();
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
  };

})(jQuery);
