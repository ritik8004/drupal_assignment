/**
 * @file
 * Custom JS for HM brand to show attributes details in overlay.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.pdp_overlay = {
    attach: function (context, settings) {
      $('.popup-details').once('attribute-sliderbar').on('click', function () {
        $('.attribute-sliderbar').addClass('attribute-sliderbar-active');

        if ($(window).width() > 1023) {
          $('html').addClass('all-filters-overlay');
        }
        else {
          $('body').addClass('mobile--overlay');
        }
      });

      // close the 'details overlay'.
      $('.attribute-sliderbar__close').once('attribute-sliderbar').on('click', function () {
        $('.attribute-sliderbar').removeClass('attribute-sliderbar-active');
        $('body').removeClass('mobile--overlay');
        $('html').removeClass('all-filters-overlay');
      });
    }
  };

})(jQuery, Drupal);
