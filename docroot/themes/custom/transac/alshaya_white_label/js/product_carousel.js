/**
 * @file
 * Product featured carousel.
 */

/* global isRTL */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productCategoryCarousel = {
    attach: function (context, settings) {
      var optionsBasket = {
        slidesToShow: 5,
        slidesToScroll: 5,
        focusOnSelect: false,
        touchThreshold: 1000,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 3,
              initialSlide: 1
            }
          }
        ]
      };

      var optionsPdp = {
        slidesToShow: 3,
        slidesToScroll: 3,
        focusOnSelect: false,
        touchThreshold: 1000,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2,
              initialSlide: 1
            }
          }
        ]
      };

      var optionsPlp = {
        slidesToShow: 3,
        slidesToScroll: 3,
        focusOnSelect: false,
        touchThreshold: 1000,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2,
              initialSlide: 1
            }
          }
        ]
      };

      var optionshp = {
        slidesToShow: 3,
        slidesToScroll: 3,
        focusOnSelect: false,
        touchThreshold: 1000,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2,
              initialSlide: 1
            }
          }
        ]
      };

      function applyRtl(ocObject, options) {
        // For tablets and mobile we don't want to apply OwlCarousel.
        if ($(window).width() < 1024) {
          return;
        }
        // Get number of items.
        var itemsCount = ocObject.find('.views-row').length;

        // Check dynamically if looping is required and at which breakpoint.
        for (var i in options.responsive) {
          if (options.responsive[i]) {
            options.responsive[i].loop = (options.responsive[i].items < itemsCount);
          }
        }

        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.slick(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.slick(options);
        }
      }

      var plpfeaturedproduct = $('.l-two--sf .paragraph--type--product-carousel-category .product-category-carousel');
      var advancedfeaturedproduct = $('.l-one--w .paragraph--type--product-carousel-category .product-category-carousel, .advanced-page-only .paragraph--type--product-carousel-category .product-category-carousel, .frontpage .paragraph--type--product-carousel-category .product-category-carousel');
      var crossSell = $('.horizontal-crossell .owl-carousel');
      var upSell = $('.horizontal-upell .owl-carousel');
      var relatedSell = $('.horizontal-related .owl-carousel');
      var basketHR = $('.block-basket-horizontal-recommendation .owl-carousel');

      plpfeaturedproduct.each(function () {
        applyRtl($(this), optionsPlp);
      });

      advancedfeaturedproduct.each(function () {
        applyRtl($(this), optionshp);
      });

      basketHR.each(function () {
        applyRtl($(this), optionsBasket);
      });

      crossSell.each(function () {
        applyRtl($(this), optionsPdp);
      });

      upSell.each(function () {
        applyRtl($(this), optionsPdp);
      });

      relatedSell.each(function () {
        applyRtl($(this), optionsPdp);
      });

      // To fix lazyload for horizontal scroll areas on devices.
      // We dont use a slider in devices.
      if ($(window).width() < 1024) {
        $('.product-category-carousel').on('touchmove', debounce(function () {
          if (typeof Drupal.blazyRevalidate !== 'undefined') {
            Drupal.blazyRevalidate();
          }
        }, 350));
      }
    }
  };
})(jQuery, Drupal);
