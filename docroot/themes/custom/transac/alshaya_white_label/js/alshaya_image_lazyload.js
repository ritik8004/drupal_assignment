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
      $(window).on('load', function(){
        Blazy({
          offset: $(window).height() // Loads images before they're visible
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
