/**
 * @file
 * This file contains code for sharethis.
 */

(function ($, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaPerformanceShareThis = {
    attach: function (context) {
      $('.sharethis-wrapper').once('load-share-this').each(function () {
        // Do it when window load finishes, resized or scrolled.
        $(window).on('load resize scroll', function () {
          Drupal.loadShareThis();
        });

        // Also do it for magazine layout.
        $('.share-icon').on('click', function () {
          Drupal.loadShareThis();
        });
      })
    }
  };

  Drupal.loadShareThis = function () {
    if ($('#sharethis-js').length === 0 && $('.sharethis-wrapper').isElementInViewPort(0)) {
      $('head').append('<script id="sharethis-js" src="https://ws.sharethis.com/button/buttons.js"></script>');
      setTimeout(Drupal.shareThisLoaded, 50);
    }
  };

  Drupal.shareThisLoaded = function () {
    if (typeof stLight !== 'undefined') {
      stLight.options(drupalSettings.sharethis);
    }
    else {
      // Try again, we do not expect this load to fail.
      setTimeout(Drupal.shareThisLoaded, 50);
    }
  };

})(jQuery, drupalSettings);
