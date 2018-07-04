/**
 * @file
 * Delivery USP.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.deliveryUsp = {
    attach: function (context, settings) {
      var timer_values = settings.usp_text_timer;
      $.each(timer_values, function (key, value) {
        var delivery_options = {
          autoplay: true,
          autoplaySpeed: value * 1000
        };
        if ($(window).width() < 1025) {
          if (isRTL()) {
            $('.paragraph_usp_delivery.paragraph-id--' + key + ' .field--name-field-usp-text.field__items').attr('dir', 'rtl');
            $('.paragraph_usp_delivery.paragraph-id--' + key + ' .field--name-field-usp-text.field__items').slick(
                $.extend({}, delivery_options, {rtl: true})
            );
          }
          else {
            $('.paragraph_usp_delivery.paragraph-id--' + key + ' .field--name-field-usp-text.field__items').slick(delivery_options);
          }
        }
      });
    }
  };

})(jQuery, Drupal);
