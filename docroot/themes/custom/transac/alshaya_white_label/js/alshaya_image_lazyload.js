/**
 * @file
 * Provides blazy lazyloading.
 */

/* global Blazy */

(function ($, Drupal) {
  'use strict';

  var blazyTimeout = null;

  Drupal.behaviors.blazy = {
    attach: function (context, settings) {
      $(window).once('blazy').each(function () {
        $(document).ajaxComplete(function () {
          Drupal.blazyRevalidate();
        });

        $(window).on('load', function () {
          Drupal.blazyRevalidate();
        });

        // Initialize.
        Drupal.blazy = new Blazy({
          offset: $(window).height(),
          success: function () {
            $(window).trigger('blazySuccess');
          }
        });
      });
    }
  };

  Drupal.blazyRevalidate = function () {
    if (typeof blazyTimeout !== 'undefined' && blazyTimeout !== null) {
      clearTimeout(blazyTimeout);
    }

    blazyTimeout = setTimeout(Drupal.blazy.revalidate, 100);
  };

})(jQuery, Drupal);
