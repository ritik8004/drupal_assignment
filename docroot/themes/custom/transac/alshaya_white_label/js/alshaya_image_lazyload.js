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
      var bLazy = new Blazy();
    }
  };
})(jQuery, Drupal, drupalSettings);
