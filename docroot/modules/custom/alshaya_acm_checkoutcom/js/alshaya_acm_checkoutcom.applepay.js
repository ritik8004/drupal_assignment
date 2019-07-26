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
  document.addEventListener('apple_pay_authorisation_fail', removeLoader,{once: true});
  document.addEventListener('apple_pay_cancel', removeLoader,{once: true});

})(jQuery, Drupal, drupalSettings);
