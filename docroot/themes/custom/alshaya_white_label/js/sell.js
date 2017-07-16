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

      var optionsPdp = {
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
      };

      if (isRTL()) {
        $('.horizontal-crossell .owl-carousel').attr('dir', 'rtl');
        $('.horizontal-crossell .owl-carousel').owlCarousel(
          $.extend({}, optionsPdp, {rtl: true})
        );
      }
      else {
        $('.horizontal-crossell .owl-carousel').owlCarousel(optionsPdp);
      }

      if (isRTL()) {
        $('.horizontal-upell .owl-carousel').attr('dir', 'rtl');
        $('.horizontal-upell .owl-carousel').owlCarousel(
          $.extend({}, optionsPdp, {rtl: true})
        );
      }
      else {
        $('.horizontal-upell .owl-carousel').owlCarousel(optionsPdp);
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
