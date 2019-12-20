/**
 * @file
 * Cart promotion code accordion.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.cartAccordion = {
    attach: function (context, settings) {
      var applyCoupon = $('#coupon-button');
      if (context === document) {
        applyCoupon.prev().addBack().wrapAll('<div class="card__content">');
      }
      $('.coupon-code-wrapper, .alias--cart #details-privilege-card-wrapper', context).once('cartaccordion').each(function () {
        if (context === document) {
          var error = $(this).find('.form-item--error-message');
          var active = false;
          if (error.length > 0) {
            active = 0;
          }
          $(this).accordion({
            header: '.card__header',
            collapsible: true,
            heightStyle: 'content',
            active: active
          });
        }
      });
    }
  };

})(jQuery, Drupal);
