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
          ocObject.hide().slick(
            $.extend({}, options, {rtl: true})
          ).fadeIn('slow');
        }
        else {
          ocObject.slick(options);
        }
      }

      // We have two selectors:
      // For V1 we apply slick js immediately after page load.
      // Fov V2 we wait until the field items are populated by RCS.
      var shopByStory = $('.field--name-field-magazine-shop-the-story:not(.rcs) .field__items, .field--name-field-magazine-shop-the-story.rcs .rcs-field__items');
      // For tablets and mobile we don't want to apply slickSlider.
      if ($(window).width() > 1023) {
        shopByStory.each(function () {
          applyRtl($(this), optionsShopByStory);
        });
      }

      var magazineHeroBanner = $('.field--name-field-magazine-hero-image.field__items');
      magazineHeroBanner.each(function () {
        applyRtl($(this), optionsHeroImageBanner);
      });
    }
  };

})(jQuery, Drupal);
