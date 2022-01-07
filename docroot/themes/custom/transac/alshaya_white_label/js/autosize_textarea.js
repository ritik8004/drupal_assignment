/**
 * @file
 * Autosize Textarea.
 */

/* global autosize */

(function ($, Drupal) {

  Drupal.behaviors.autoSizeTextarea = {
    attach: function (context, settings) {
      var textarea = $('textarea');
      var textareaSuffix = $('.form-type-textarea .field-suffix');

      autosize(textarea);

      // Decrement suffix count on input.
      textarea.on('input', function () {
        var remaining = 220 - parseInt($(this).val().length);
        textareaSuffix.text(remaining);
      });
    }
  };
})(jQuery, Drupal);
