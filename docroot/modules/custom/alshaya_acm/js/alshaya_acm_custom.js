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

      $('[data-drupal-selector="customer-cart-form"]').once('bind-events').each(function () {
        $('#apply_coupon', $(this)).on('click', function () {
          $('[data-drupal-selector="edit-update"]', $(this)).trigger('click');
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
