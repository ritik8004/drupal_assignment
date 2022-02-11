/**
 * @file
 * This file contains code for sharethis.
 */

(function ($, drupalSettings) {

  var shareThisLoadingCounter = 0;

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
      setTimeout(Drupal.shareThisLoaded, 1);
    }
  };

  Drupal.shareThisLoaded = function () {
    if (typeof stLight !== 'undefined') {
      stLight.options(drupalSettings.sharethis);
    }
    else {
      shareThisLoadingCounter++;

      // Let's try only 10 times. We increase time with every counter so
      // we are going to try for three seconds at-least which is good enough.
      if (shareThisLoadingCounter > 10) {
        return;
      }

      // Try again, we do not expect this load to fail.
      setTimeout(Drupal.shareThisLoaded, 100 * shareThisLoadingCounter);
    }
  };

})(jQuery, drupalSettings);
