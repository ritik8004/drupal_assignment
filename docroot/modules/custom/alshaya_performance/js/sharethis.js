/**
 * @file
 * This file contains code for sharethis.
 */

(function ($, drupalSettings) {
  'use strict';

  var elementClicked = null;

  Drupal.behaviors.alshayaPerformanceShareThis = {
    attach: function (context) {
      $('.sharethis-wrapper, .sharethis-wrapper span').once('bind-js').on('click', function (event) {
        // Load the script if not loaded already.
        if ($('#sharethis-js').length === 0) {
          event.preventDefault();
          event.stopPropagation();

          elementClicked = $(this);
          $('head').append('<script id="sharethis-js" src="https://ws.sharethis.com/button/buttons.js"></script>');

          // Update sharethis options from drupalSettings after external
          // JS is loaded.
          setTimeout(Drupal.shareThisLoaded, 50);
        }
      });
    }
  };

  Drupal.shareThisLoaded = function () {
    if (typeof stLight !== 'undefined') {
      stLight.options(drupalSettings.sharethis);

      // Trigger click again for element originally clicked by user.
      setTimeout(function () {
        if (elementClicked !== null) {
          $(elementClicked).trigger('click');
          elementClicked = null;
        }
      }, 1);
    }
    else {
      // Try again, we do not expect this load to fail.
      setTimeout(Drupal.shareThisLoaded, 50);
    }
  };

})(jQuery, drupalSettings);
