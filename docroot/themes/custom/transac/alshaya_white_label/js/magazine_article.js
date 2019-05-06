/**
 * @file
 * Magazine article related js.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.MagazineShoptheStorySlider = {
    attach: function (context, settings) {
      var optionsshopbyStory = {
        arrows: true,
        useTransform: false,
        slidesToShow: 5,
        slidesToScroll: 1,
        focusOnSelect: false,
        touchThreshold: 1000,
        infinite: false
      };

      var optionsheroimageBanner = {
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

      var shopbyStory = $('.field--name-field-magazine-shop-the-story .field__items');
      var magazineHerobanner = $('.field--name-field-magazine-hero-image.field__items');

      // For tablets and mobile we don't want to apply slickSlider.
      if ($(window).width() > 1023) {
        shopbyStory.each(function () {
          applyRtl($(this), optionsshopbyStory);
        });
      }

      magazineHerobanner.each(function () {
        applyRtl($(this), optionsheroimageBanner);
      });
    }
  };

})(jQuery, Drupal);
