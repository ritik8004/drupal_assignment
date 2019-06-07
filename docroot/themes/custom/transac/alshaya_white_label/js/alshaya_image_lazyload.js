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
        offset: 370 // Loads images 100px before they're visible
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
