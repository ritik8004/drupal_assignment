/**
 * @file
 * Clear autofill data.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.clearAutoFillData = function () {
    if ($('.form-item--error-message, label.error').length > 0) {
      return;
    }

    $('input[type="email"]').val('');
    $('input[type="password"]').val('');
  };

  Drupal.clearAutoFillData();

  setTimeout(Drupal.clearAutoFillData, 20);

  $(window).load(function () {
    Drupal.clearAutoFillData();
  });

})(jQuery, Drupal);
