/**
 * @file
 * Sell.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {

      var crossSell = $('.horizontal-crossell .owl-carousel');
      var upSell = $('.horizontal-upell .owl-carousel');
      var basketHR = $('.block-basket-horizontal-recommendation .owl-carousel');

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

      function applyRtl(ocObject, options) {
        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.owlCarousel(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.owlCarousel(options);
        }
      }

      applyRtl(basketHR, options);
      applyRtl(crossSell, optionsPdp);
      applyRtl(upSell, optionsPdp);

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
