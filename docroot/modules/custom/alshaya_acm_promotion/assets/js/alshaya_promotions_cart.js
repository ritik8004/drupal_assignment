/**
 * @file
 * Handles cart level promotions.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaPromotionsCartCodes = {
    attach: function (context) {
      var customer_cart_form = $('form.customer-cart-form', context);
      $('.promotion-available-code > .available', customer_cart_form).on('click', function () {
        var coupon = $('.promotion-available-code > .available', customer_cart_form).text();
        $('input[name="coupon"]', customer_cart_form).val(coupon);
        customer_cart_form.submit();
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
