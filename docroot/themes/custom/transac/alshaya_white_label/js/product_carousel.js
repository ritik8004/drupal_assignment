/**
 * @file
 * Product featured carousel.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  /**
   * Call blazyRevalidate() on afterChange of slick sliders.
   */
  function applyHorizontalLazyLoad(carousel) {
    // Lazy Load on carousels.
    carousel.on('afterChange', function () {
      Drupal.blazyRevalidate();
    });
  }

  Drupal.behaviors.productCategoryCarousel = {
    attach: function (context, settings) {
      var pdp_items_desk = drupalSettings.pdp_items_desk;
      var basket_carousel_items = drupalSettings.basket_carousel_items;
      var dp_product_carousel_items = drupalSettings.dp_product_carousel_items;
      var hp_product_carousel_items = drupalSettings.hp_product_carousel_items;

      var optionsBasket = {
        slidesToShow: basket_carousel_items,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000
      };

      var optionsPdp = {
        slidesToShow: pdp_items_desk,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000
      };

      var optionsPlp = {
        slidesToShow: dp_product_carousel_items,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000
      };

      var optionshp = {
        slidesToShow: hp_product_carousel_items,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000
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
          ocObject.once().slick(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.once().slick(options);
        }
      }

      var plpfeaturedproduct = $('.l-two--sf .paragraph--type--product-carousel-category .product-category-carousel');
      var advancedfeaturedproduct = $('.l-one--w .paragraph--type--product-carousel-category .product-category-carousel, .advanced-page-only .paragraph--type--product-carousel-category .product-category-carousel, .frontpage .paragraph--type--product-carousel-category .product-category-carousel');
      var crossSell = $('.horizontal-crossell .owl-carousel');
      var upSell = $('.horizontal-upell .owl-carousel');
      var relatedSell = $('.horizontal-related .owl-carousel');
      var basketHR = $('.block-basket-horizontal-recommendation .owl-carousel');
      var productCarouselClasses = ['.product-category-carousel', '.owl-carousel'];

      plpfeaturedproduct.each(function () {
        applyRtl($(this), optionsPlp);
        applyHorizontalLazyLoad($(this));
      });

      advancedfeaturedproduct.each(function () {
        applyRtl($(this), optionshp);
        applyHorizontalLazyLoad($(this));
      });

      basketHR.each(function () {
        applyRtl($(this), optionsBasket);
        applyHorizontalLazyLoad($(this));
      });

      crossSell.each(function () {
        applyRtl($(this), optionsPdp);
        applyHorizontalLazyLoad($(this));
      });

      upSell.each(function () {
        applyRtl($(this), optionsPdp);
        applyHorizontalLazyLoad($(this));
      });

      relatedSell.each(function () {
        applyRtl($(this), optionsPdp);
        applyHorizontalLazyLoad($(this));
      });

      // We dont have carousel in tablets & phones,
      // Enable horizontal lazy load.
      if ($(window).width() < 1024) {
        // Horizontal Lazy load for scroll areas.
        $.each(productCarouselClasses, function (key, scrollArea) {
          Drupal.blazyHorizontalLazyLoad(scrollArea);
        });
      }
      $('.nodetype--acq_product .owl-carousel .above-mobile-block, .path--cart .owl-carousel .above-mobile-block', context).once().on('click', function () {
        // Adjust the positioning of the throbber as per the transform property on slick-track.
        if ($(window).width() > 1023) {
          var sliderTrackTransform = $(this).parents('.slick-track').css('transform').replace(/[^0-9\-.,]/g, '').split(',');
          var sliderWrapperWidth = $(this).parents('.owl-carousel').css('width');
          if (isRTL()) {
            $('.ajax-progress-throbber').css({'transform': 'translate3d(' + -Math.abs(sliderTrackTransform[4]) + 'px, 0px, 0px)', 'max-width': sliderWrapperWidth});
          }
          else {
            $('.ajax-progress-throbber').css({'transform': 'translate3d(' + Math.abs(sliderTrackTransform[4]) + 'px, 0px, 0px)', 'max-width': sliderWrapperWidth});
          }
        }
      });
    }
  };
})(jQuery, Drupal);
