/**
 * @file
 * Autosize Textarea.
 */

/* global autosize */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {
      var textarea = $('textarea');
      autosize(textarea);
    }
  };
})(jQuery, Drupal);
