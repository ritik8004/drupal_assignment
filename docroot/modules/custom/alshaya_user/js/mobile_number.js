/**
 * @file
 * For mobile number.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.mobileNumber = {
    attach: function (context, settings) {
      // Remove mobile number flag and braces.
      if ($('.country-select').length) {
        $('.country-select').hide();
      }
    }
  };

})(jQuery, Drupal);
