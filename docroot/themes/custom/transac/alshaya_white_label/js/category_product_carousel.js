/**
 * @file
 * product featured carousel.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sell = {
    attach: function (context, settings) {

      var plpfeaturedproduct = $('.nodetype--advanced_page .paragraph--type--product-carousel-category .owl-carousel');
      var advancedfeaturedproduct = $('.frontpage .paragraph--type--product-carousel-category .owl-carousel');

      var options = {
        responsiveClass: true,
        dots: true,
        loop: true,
        responsive: {
          0: {
            items: 2,
            nav: true,
            stagePadding: 25,
            mouseDrag: true
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

      var optionshp = {
        loop: true,
        responsiveClass: true,
        dots: true,
        responsive: {
          0: {
            items: 2,
            nav: false,
            stagePadding: 25,
            mouseDrag: true
          },
          768: {
            items: 4,
            nav: true
          },
          1025: {
            items: 6,
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

      applyRtl(plpfeaturedproduct, options);
      applyRtl(advancedfeaturedproduct, optionshp);

      $('.owl-carousel').owlCarousel({
        loop: true,
        responsiveClass: true,
        dots: true,
        responsive: {
          0: {
            items: 2,
            nav: false,
            stagePadding: 25,
            mouseDrag: true
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
    }
  };
})(jQuery, Drupal);
