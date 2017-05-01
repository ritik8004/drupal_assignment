/**
 * @file
 * Format Input.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formatInput = {
    attach: function (context, settings) {
      var privilegeCard = $('.c-input__privilege-card');
      privilegeCard.on('input', function () {
        var value = $(this).val();
        if (value.length > 0) {
          $(this).val(value.match(/\d{4}(?=\d{1,4})|\d+/g).join('-'));
        }
      });
    }
  };
})(jQuery, Drupal);
