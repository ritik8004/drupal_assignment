/**
 * @file
 * Custom JS for HM brand to show attributes details in overlay.
 */

(function ($, Drupal) {

  Drupal.behaviors.pdp_overlay_attributes = {
    attach: function (context, settings) {
      $('.pdp-overlay-details').once('attribute-sliderbar').on('click', function () {
        $('.attribute-sliderbar').addClass('attribute-sliderbar-active pdp-addon-attr-overlay');

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
