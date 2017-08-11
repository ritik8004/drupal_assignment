/**
 * @file
 * Overriding email address validator.
 */

(function ($, Drupal) {
  'use strict';

  $.validator.methods.email = function (value, element) {
    return this.optional(element) || /[a-z]+@[a-z]+\.[a-z]+/.test(value);
  };
})(jQuery, Drupal);
