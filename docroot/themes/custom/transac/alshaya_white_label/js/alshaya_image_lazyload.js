/**
 * @file
 * Provides blazy lazyloading.
 */

/* global Blazy */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.blazy = {
    attach: function (context, settings) {
      // Initialize.
      var blazyOptions = {offset: $(window).height()};
      $(document).ajaxComplete(function () {
        Blazy(blazyOptions);
      });
      $(window).on('load', function () {
        Blazy(blazyOptions);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
