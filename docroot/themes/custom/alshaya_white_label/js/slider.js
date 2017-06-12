/**
 * @file
 * Sliders.
 */

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
        autoplaySpeed: 500,
        dots: true
      };

      if (isRTL()) {
        $('.c-slider-promo__items').attr('dir', 'rtl');
        $('.c-slider-promo__items').slick(
           $.extend({}, options, {rtl: true})
        );
      }
      else {
        $('.c-slider-promo__items').slick(options);
      }
    }
  };

})(jQuery, Drupal);
