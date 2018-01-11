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

    $('input[type="email"], input[type="password"]').each(function () {
      if (!($(this).prop('readonly') || $(this).prop('disabled'))) {
        $(this).val('');
      }
    });
  };

  Drupal.clearAutoFillData();

  setTimeout(Drupal.clearAutoFillData, 20);

  $(window).on('load',function () {
    Drupal.clearAutoFillData();
  });

})(jQuery, Drupal);
