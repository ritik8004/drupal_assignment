/**
 * @file
 * Handles cart level promotions.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaPromotionsCartCodes = {
    attach: function (context) {
      $('.promotion-coupon-code.available').once('alshayaPromotionsCartCodes').on('click', function () {
        // Get coupon from data attribute to ensure we copy it as is.
        var coupon = $(this).attr('data-coupon-code');

        // We will have only one cart form.
        var form = $('form.customer-cart-form');

        // Add coupon to input.
        $('input[name="coupon"]', form).focus().val(coupon);

        // Submit form.
        form.submit();
      });
    }
  };

})(jQuery, Drupal);
