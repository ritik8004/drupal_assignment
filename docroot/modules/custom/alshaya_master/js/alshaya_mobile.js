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
        // Set the data prefix once.
        $(this).data('prefix', $(this).attr('mobile-prefix'));

        Drupal.alshayaMobileNumber.init($(this), $(this).val().toString().trim());
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
