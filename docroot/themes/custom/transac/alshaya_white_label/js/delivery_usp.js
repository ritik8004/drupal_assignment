/**
 * @file
 * Delivery USP.
 */

/* global isRTL */

(function ($, Drupal) {

  Drupal.behaviors.deliveryUsp = {
    attach: function (context, settings) {
      var timer_values = settings.usp_text_timer;
      $.each(timer_values, function (key, value) {
        var delivery_options = {
          autoplay: true,
          touchThreshold: 1000,
          autoplaySpeed: value * 1000
        };
        if ($(window).width() < 1025) {
          if (isRTL()) {
            $('.paragraph_usp_delivery.paragraph-id--' + key + ' .field--name-field-usp-text.field__items').attr('dir', 'rtl');
            $('.paragraph_usp_delivery.paragraph-id--' + key + ' .field--name-field-usp-text.field__items').once('initiate-slick').slick(
              $.extend({}, delivery_options, {rtl: true})
            );
          }
          else {
            $('.paragraph_usp_delivery.paragraph-id--' + key + ' .field--name-field-usp-text.field__items').once('initiate-slick').slick(delivery_options);
          }
        }
      });
    }
  };

})(jQuery, Drupal);
