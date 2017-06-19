/**
 * @file
 * Sliders.
 */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  function isRTL() {
    var html = $('html');
    var dir = html.attr('dir');
    if (typeof dir === 'undefined' || dir === 'ltr') {
      return false;
    }
    else {
      return true;
    }
  }

  Drupal.behaviors.sliderBanner = {
    attach: function (context, settings) {
      var options = {
        arrows: true,
        autoplay: true,
        autoplaySpeed: 15000,
        dots: true
      };

      function centerDots() {
        var parent = $('.c-slider-promo__items');
        var dots = $('.slick-dots');
        var button = $('.slick-next, .slick-prev');

        var parentHeight = parent.height();
        var dotsHeight = dots.height() + (16 * 2);
        var buttonHeight = button.height() / 2;
        var windowWidth = $(window).width();

        var center;
        if (windowWidth > 767) {
          center = (parentHeight - buttonHeight) / 2;
        }
        else {
          center = (parentHeight - (dotsHeight + buttonHeight)) / 2;
        }
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
      }, 250));

      var windowWidth = $(window).width();
      setTimeout(function () {
        $(window).width(windowWidth);
        centerDots();
      }, 500);
    }
  };

})(jQuery, Drupal);
