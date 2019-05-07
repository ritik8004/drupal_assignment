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
      Blazy();
    }
  };
})(jQuery, Drupal, drupalSettings);
