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

      var promoPanelOptions = {
        useTransform: false,
        slidesToShow: 3,
        slidesToScroll: 1,
        focusOnSelect: false,
        centerMode: true,
        centerPadding: '190px',
        initialSlide: 1,
        touchThreshold: 1000,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 2,
              centerPadding: '10px',
              slidesToScroll: 1
            }
          }
        ]
      };

      if (isRTL()) {
        $('.block-promo-panel-wrapper > .field--name-field-paragraph-content').attr('dir', 'rtl');
        $('.block-promo-panel-wrapper > .field--name-field-paragraph-content').slick(
           $.extend({}, promoPanelOptions, {rtl: true})
        );
      }
      else {
        $('.block-promo-panel-wrapper > .field--name-field-paragraph-content').slick(promoPanelOptions);
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
