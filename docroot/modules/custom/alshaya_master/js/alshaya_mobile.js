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

        // Set the data prefix once.
        element.data('prefix', element.attr('mobile-prefix'));

        Drupal.alshayaMobileNumber.init(element, element.val().toString().trim());

        element.numeric({
          allowMinus   : false,
          allowThouSep : false,
          allowPlus : true,
          allowDecSep: false
        });

      });
    }
  };

  // Init/Reset inputprefix js for given field.
  Drupal.alshayaMobileNumber.init = function (element, value) {
    if (value) {
      value = value.replace(element.attr('mobile-prefix'), '');
      element.val(element.attr('mobile-prefix') + value);
    }
    element.inputprefix();
  };

})(jQuery, Drupal);
