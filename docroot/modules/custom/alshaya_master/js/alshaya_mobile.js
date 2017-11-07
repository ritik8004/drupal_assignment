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
      var prefix = $('.form-item-mobile-number-country-code .prefix').html().replace(/[{()}]/g, '');
      $('.form-item-mobile-number-country-code .prefix').replaceWith('<div class="prefix">'+ prefix +'</div>');
      $('.mobile-number-field .form-item-mobile-number-country-code, .mobile-number-field .form-item-mobile-number-mobile')
          .wrapAll('<div class="mobile-input--wrapper"></div>');

      if ($('label#edit-mobile-number-mobile-error')) {
        $('#edit-mobile-number').addClass('error-mobile');
      }
      else if ($('label#edit-mobile-number-mobile-error').style('display', 'none')) {
        $('#edit-mobile-number').removeClass('error-mobile');
      }
    }
  };
})(jQuery, Drupal);
