/**
 * @file
 * Magazine article related js.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.magazineArticleSlider = {
    attach: function (context, settings) {
      var optionsShopByStory = {
        arrows: true,
        useTransform: false,
        slidesToShow: 5,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000,
        infinite: false,
        accessibility: true,
        speed: 300,
        responsive: [
          {
            breakpoint: 930,
            settings: {
              slidesToShow: 4,
              slidesToScroll: 1,
              infinite: true,
              dots: false,
            }
          },
          {
            breakpoint: 790,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 1,
            }
          },
          {
            breakpoint: 650,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 1,
            }
          },
          {
            breakpoint: 400,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1,
            }
          }
        ]
      };

      var optionsHeroImageBanner = {
        arrows: true,
        useTransform: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000,
        infinite: false,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              arrows: false,
              dots: true
            }
          }
        ]
      };

      function applyRtl(ocObject, options) {
        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.hide().slick(
            $.extend({}, options, {rtl: true})
          ).fadeIn('slow');
        }
        else {
          ocObject.slick(options);
        }
      }

      // If RCS is not enabled, we apply slick js immediately after page load.
      var s1 = '.field--name-field-magazine-shop-the-story:not(.rcs) .field__items';

      // When RCS is enabled, we wait until the field items are populated by RCS.
      var s2 = '.field--name-field-magazine-shop-the-story.rcs .rcs-field__items';

      var shopByStory = $(s1 + ', ' + s2);
      shopByStory.each(function () {
        applyRtl($(this), optionsShopByStory);
      });

      var magazineHeroBanner = $('.field--name-field-magazine-hero-image.field__items');
      magazineHeroBanner.each(function () {
        applyRtl($(this), optionsHeroImageBanner);
      });
    }
  };

})(jQuery, Drupal);
