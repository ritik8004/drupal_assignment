(function ($) {
  Drupal.behaviors.alshaya_acm_js = {
    attach: function (context, settings) {
      $('#checkout-top').bind('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $('[data-drupal-selector="edit-checkout"]').trigger('click');
      });

      $(".acq-cart-summary .content-items").slideUp();

      $(".acq-cart-summary .content-head").on('click', function() {
        $(this).parent().toggleClass("active--accordion");
        $(this).next().slideToggle();
      });

      $('#apply_coupon').on('click', function () {
        $('input[name="coupon"][type="hidden"]').val($('#edit-promotion').val());
        $('#edit-update').trigger('click');
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
