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
          success: function (element) {
            $(window).trigger('blazySuccess', element);
          }
        });
      });
    }
  };

  Drupal.blazyRevalidate = function () {
    if (typeof blazyTimeout !== 'undefined' && blazyTimeout !== null) {
      clearTimeout(blazyTimeout);
    }

    if (typeof Drupal.blazy !== 'undefined') {
      blazyTimeout = setTimeout(Drupal.blazy.revalidate, 100);
    }
  };

  /**
   * Enables Blazy Lazy loading for horizontal scroll areas.
   *
   * @param {*} container
   * The horizontal scroll area.
   */
  Drupal.blazyHorizontalLazyLoad = function (container) {
    new Blazy({
      container: container
    });
  };

})(jQuery, Drupal);
