/**
 * @file
 * Sell.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {

      $('.block-basket-horizontal-recommendation .owl-carousel').owlCarousel({
        loop: true,
        responsiveClass: true,
        dots: false,
        responsive: {
          0: {
            items: 2,
            nav: false
          },
          768: {
            items: 4,
            nav: true
          },
          1024: {
            items: 5,
            nav: true
          }
        }
      });

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
