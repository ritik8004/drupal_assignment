/**
 * @file
 * Attaches behaviors for the alshaya apple pay.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  const removeLoader = function (e) {
    $(this).removeCheckoutLoader();
  };

  // Remove loader to allow user to edit form on error.
  $(document).on('apple_pay_authorisation_fail', removeLoader);
  $(document).on('apple_pay_cancel', removeLoader);

})(jQuery, Drupal, drupalSettings);
