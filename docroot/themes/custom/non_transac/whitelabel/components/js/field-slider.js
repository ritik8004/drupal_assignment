/**
 * @file
 * Sliders.
 */

/* global debounce */

/* eslint-disable */
function isRTL() {
  /* eslint-enable */

  var html = jQuery('html');
  var dir = html.attr('dir');
  if (typeof dir === 'undefined' || dir === 'ltr') {
    return false;
  }
  else {
    return true;
  }
}

(function ($, Drupal) {

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

      if ($('.field-slider-items-wrapper').find('.paragraph-t-banner').children().length > 0) {
        if (isRTL()) {
          $('.field-slider-items-wrapper').attr('dir', 'rtl');
          $('.field-slider-items-wrapper').once('initiate-slick').slick(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          $('.field-slider-items-wrapper').once('initiate-slick').slick(options);
        }
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
