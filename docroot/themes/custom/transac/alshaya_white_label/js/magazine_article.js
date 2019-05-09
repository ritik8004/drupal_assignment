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
        infinite: false
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
          ocObject.slick(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.slick(options);
        }
      }

      var shopByStory = $('.field--name-field-magazine-shop-the-story .field__items');
      var magazineHeroBanner = $('.field--name-field-magazine-hero-image.field__items');

      // For tablets and mobile we don't want to apply slickSlider.
      if ($(window).width() > 1023) {
        shopByStory.each(function () {
          applyRtl($(this), optionsShopByStory);
        });
      }

      magazineHeroBanner.each(function () {
        applyRtl($(this), optionsHeroImageBanner);
      });
    }
  };

})(jQuery, Drupal);
