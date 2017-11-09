/**
 * @file
 * Custom JS to mobile number field to have prefix.
 */

/**
 * @namespace
 */
Drupal.alshayaMobileNumber = Drupal.alshayaMobileNumber || {};

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaMobileNumber = {
    attach: function (context, settings) {
      $('[mobile-prefix]').once('bind-js').each(function () {
        var element = $(this);
        element.numeric({
          allowMinus   : false,
          allowThouSep : false,
          allowPlus : true,
          allowDecSep: false
        });

      });
      var prefix = $('.mobile-number-field .prefix').html().replace(/[{()}]/g, '');
      $('.mobile-number-field .prefix').replaceWith('<div class="prefix">'+ prefix +'</div>');
      $('.mobile-number-field .form-type-select, .mobile-number-field .form-type-textfield')
          .once()
          .wrapAll('<div class="mobile-input--wrapper"></div>');
    }
  };
})(jQuery, Drupal);
