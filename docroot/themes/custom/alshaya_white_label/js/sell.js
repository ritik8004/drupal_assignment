/**
 * @file
 * Sell.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {
      var options = {
        loop: true,
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
      };

      if (isRTL()) {
        $('.block-basket-horizontal-recommendation .owl-carousel').attr('dir', 'rtl');
        $('.block-basket-horizontal-recommendation .owl-carousel').owlCarousel(
          $.extend({}, options, {rtl: true})
        );
      }
      else {
        $('.block-basket-horizontal-recommendation .owl-carousel').owlCarousel(options);
      }

      $('.owl-carousel').owlCarousel({
        loop: true,
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
