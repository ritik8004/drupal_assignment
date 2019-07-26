/**
 * @file
 * Provides blazy lazyloading.
 */

/* global Blazy */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.blazy = {
    attach: function (context, settings) {
      $(document).ajaxComplete(function () {
        Drupal.blazy.revalidate();
      });
      $(window).on('load', function () {
        Drupal.blazy.revalidate();
      });

      // Initialize.
      Drupal.blazy = new Blazy({
        offset: $(window).height(),
        success: function () {
          $(window).trigger('blazySuccess');
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
