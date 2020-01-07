/**
 * @file
 * Handles cart level promotions.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaPromotionsCartCodes = {
    attach: function (context) {
      var customer_cart_form = $('form.customer-cart-form', context);
      $('.promotion-coupon-code.available', context).on('click', function () {
        var coupon = $('.promotion-coupon-code.available', customer_cart_form).text();
        $('input[name="coupon"]', customer_cart_form).focus().val(coupon);
        customer_cart_form.submit();
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
