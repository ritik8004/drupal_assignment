/**
 * @file
 * Provides blazy lazyloading.
 */

(function ($, Drupal) {
  'use strict';

  /* global Blazy */

  Drupal.behaviors.blazy = {
    attach: function (context, settings) {
      // Initialize.
      var bLazy = new Blazy({
        success: function () {
          updateCounter();
        }
      });

      // Not needed, only here to illustrate amount of loaded images.
      var imageLoaded = 0;
      console.log(imageLoaded);

      function updateCounter() {
        imageLoaded++;
        console.log(imageLoaded);
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
