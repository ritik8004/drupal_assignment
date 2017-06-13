(function ($) {
  Drupal.behaviors.alshaya_acm_js = {
    attach: function (context, settings) {
      $('.acq-cart-summary').once('bind-events').each(function () {
        $('.content-items', $(this)).slideUp();

        $('.content-head', $(this)).on('click', function() {
          $(this).parent().toggleClass('active--accordion');
          $(this).next().slideToggle();
        });
      });

      // Hide apply coupon button on page load.
      $('#apply_coupon').hide();

      $('[data-drupal-selector="customer-cart-form"]', context).once('bind-events').each(function () {
        // Display apply coupon button if there's a value, else hide it.
        $('input[name="coupon"]').on('input', function (e) {
          if ($(this).val() != '') {
            $('#apply_coupon').show();
          }
          else {
            $('#apply_coupon').hide();
          }
        });

        $('#apply_coupon', $(this)).on('click', function () {
          $('[data-drupal-selector="edit-update"]').trigger('click');
        });

        $('#checkout-top', $(this)).on('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          $('[data-drupal-selector="edit-checkout"]').trigger('click');
        });
      });

      $.fn.updateOutOfStockDom = function(message) {
        if ($('#out-of-stock-message').length) {
          $('#out-of-stock-message').html(message);
        }
        else {
          var error_div = '<div id="out-of-stock-message">' + message + '</div>';
          $('#table-cart-items').before(error_div);
        }
      };

    }
  };
})(jQuery);
