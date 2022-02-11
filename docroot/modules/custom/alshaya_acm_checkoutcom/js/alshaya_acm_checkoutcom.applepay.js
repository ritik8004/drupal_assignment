/**
 * @file
 * Attaches behaviors for the alshaya apple pay.
 */

(function ($, Drupal, drupalSettings) {

  // Remove loader to allow user to edit form on error.
  $(document).on('apple_pay_authorisation_fail apple_pay_cancel checkoutcom_form_error', function (e) {
    $(this).removeCheckoutLoader();
  });

})(jQuery, Drupal, drupalSettings);
