/**
 * @file
 * Sell.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {

      $('.block-basket-horizontal-recommendation .owl-carousel').owlCarousel({
        loop: false,
        responsiveClass: true,
        dots: true,
        responsive: {
          0: {
            items: 1,
            nav: false
          },
          768: {
            items: 4,
            nav: true
          },
          1025: {
            items: 5,
            nav: true
          }
        }
      });

      $('.owl-carousel').owlCarousel({
        loop: false,
        responsiveClass: true,
        dots: true,
        responsive: {
          0: {
            items: 1,
            nav: false
          },
          768: {
            items: 2,
            nav: true
          },
          1025: {
            items: 3,
            nav: true
          }
        }
      });

    }
  };
})(jQuery, Drupal);
