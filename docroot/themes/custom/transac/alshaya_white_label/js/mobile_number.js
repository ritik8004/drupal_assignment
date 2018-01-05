/**
 * @file
 * Set mobile number field.
 */

/* global Cleave */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.mobile_number = {
    attach: function (context, settings) {

      var defaultValue = '+' + settings.alshaya_mobile_prefix;
      var mobileNumberField = $('.mobile-number-field input');

      mobileNumberField.toArray().forEach(function (field) {
        new Cleave(field, {
          prefix: defaultValue,
          delimiter: ' ',
          blocks: [defaultValue.length, 100]
        });
      });
    }
  };
})(jQuery, Drupal);
