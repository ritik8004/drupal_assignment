(function ($) {
  Drupal.behaviors.alshaya_acm_js = {
    attach: function (context, settings) {
      $(".acq-cart-summary .content").accordion({
        collapsible: true
      });

      $('#apply_coupon').on('click', function () {
        $('input[name="coupon"][type="hidden"]').val($('#edit-promotion').val());
        $('#edit-update').trigger('click');
      });
    }
  };
})(jQuery);
