/**
 * @file
 * User login.
 */

(function ($, Drupal) {
  'use strict';

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

})(jQuery, Drupal);
