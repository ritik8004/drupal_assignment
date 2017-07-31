/**
 * @file
 * User login.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.alshayaMobileNumber = Drupal.alshayaMobileNumber || {};

  Drupal.behaviors.alshayaMobileNumber = {
    attach: function (context, settings) {
      $('input[mobile-prefix]').once('bind-js').each(function () {
        var value = $(this).val().toString().trim();
        if (value) {
          value = value.replace($(this).attr('mobile-prefix'), '');
          $(this).val($(this).attr('mobile-prefix') + value);
        }

        $(this).data('prefix', $(this).attr('mobile-prefix'));
        $(this).inputprefix();
      });
    }
  };

  // Forcefully initiate inputprefix js for given field.
  Drupal.alshayaMobileNumber.init = function (element, value) {
    if (value) {
      value = value.replace(element.attr('mobile-prefix'), '');
      element.val(element.attr('mobile-prefix') + value);
    }
    element.inputprefix();
  };

})(jQuery, Drupal);
