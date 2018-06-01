/**
 * @file
 * Sliders.
 */

/* global isRTL */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sliderBanner = {
    attach: function (context, settings) {
      var options = {
        arrows: true,
        autoplay: true,
        autoplaySpeed: 15000,
        dots: true,
        touchThreshold: 1000,
        // Fixes the blink issue:
        // https://github.com/kenwheeler/slick/issues/1890
        useTransform: false
      };

      function centerDots() {
        var parent = $('.slick-list');
        var button = $('.slick-next, .slick-prev');

        var parentHeight = parent.height();
        var buttonHeight = button.height();

        var center = (parentHeight / 2) - (buttonHeight / 2);
        button.css({top: center});
      }

      if (isRTL()) {
        $('.c-slider-promo__items').attr('dir', 'rtl');
        $('.c-slider-promo__items').slick(
           $.extend({}, options, {rtl: true})
        );
      }
      else {
        $('.c-slider-promo__items').slick(options);
      }
      // eslint-disable-next-line.
      $(window).resize(debounce(function () {
        centerDots();
      }, 500));

      var windowWidth = $(window).width();
      setTimeout(function () {
        $(window).width(windowWidth);
        centerDots();
      }, 500);
    }
  };

})(jQuery, Drupal);
