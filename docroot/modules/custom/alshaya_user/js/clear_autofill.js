/**
 * @file
 * Clear autofill data.
 */

(function ($, Drupal) {

  Drupal.clearAutoFillData = function () {
    // Do nothing if there is error.
    if ($('.form-item--error-message, label.error').length > 0) {
      return;
    }

    // Do nothing for logged in users.
    if ($('body').hasClass('user-logged-in')) {
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
