/**
 * @file
 * Sliders.
 */

/* global isRTL */

/* global debounce */

(function ($, Drupal) {

  // Call centerDots() to apply slick dots vertically center aligned.
  function centerDots() {
    var parent = $('.slick-list');
    var button = $('.slick-next, .slick-prev');

    var parentHeight = parent.height();
    var buttonHeight = button.height();

    var center = (parentHeight / 2) - (buttonHeight / 2);
    button.css({top: center});
  }

  // Call applyBannerRtl() to initialise slick.
  function applyBannerRtl(ocObject, options) {
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

      var paragraphBanner = $('.paragraph-banner', context).once('initiate-slick');
      var bannerPanelFieldItem = $('.block-promo-panel-wrapper > .field--name-field-paragraph-content > .field__item', context).once('initiate-slick');
      var bannerSliderContainer = $('.block-promo-panel-wrapper > .field--name-field-paragraph-content', context).once('initiate-slick');

      applyBannerRtl(paragraphBanner, options);
      var promoPanelOptions = {
        slidesToShow: 3,
        slidesToScroll: 3,
        focusOnSelect: false,
        touchThreshold: 1000,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2
            }
          }
        ]
      };

      if (bannerPanelFieldItem.length) {
        applyBannerRtl(bannerSliderContainer, promoPanelOptions);
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
