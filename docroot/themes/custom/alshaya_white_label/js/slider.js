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

      // Open homepage slider link in new tab.
      // This is used to fix behaviour of UC browser.
      var sliderLinks = $('.c-slider-promo a');
      sliderLinks.each(function () {
        $(this).click(function (e) {
          e.preventDefault();
          e.stopPropagation();
          var link = $(this).attr('href');
          window.open(link);
        });
      });
    }
  };

})(jQuery, Drupal);
