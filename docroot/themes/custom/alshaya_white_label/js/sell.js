/**
 * @file
 * Sell.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {

      $('.owl-carousel').owlCarousel({
        loop: true,
        responsiveClass: true,
        dots: false,
        responsive: {
          0: {
            items: 1,
            nav: false
          },
          768: {
            items: 2,
            nav: true
          },
          1024: {
            items: 3,
            nav: true
          }
        }
      });

    }
  };
})(jQuery, Drupal);
