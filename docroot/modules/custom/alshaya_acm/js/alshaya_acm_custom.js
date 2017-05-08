(function ($) {
  Drupal.behaviors.alshaya_acm_js = {
    attach: function (context, settings) {
      $(".acq-cart-summary .content-items").slideUp();

      $(".acq-cart-summary .content-head").click(function() {
        $(this).parent().toggleClass("active--accordion");
        $(this).next().slideToggle();
      });

      $('#apply_coupon').on('click', function () {
        $('input[name="coupon"][type="hidden"]').val($('#edit-promotion').val());
        $('#edit-update').trigger('click');
      });
    }
  };
})(jQuery);
