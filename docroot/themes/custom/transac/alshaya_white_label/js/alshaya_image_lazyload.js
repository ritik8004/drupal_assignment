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
      Blazy({
        offset: 570 // Loads images before they're visible
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
