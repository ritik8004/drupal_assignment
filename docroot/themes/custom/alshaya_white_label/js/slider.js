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
        items: 1,
        dots: true,
        nav: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true
      };

      if (isRTL()) {
        $('.owl-carousel').owlCarousel(
          $.extend({}, options, {rtl: true})
        );
      }
      else {
        $('.owl-carousel').owlCarousel(options);
      }
    }
  };

})(jQuery, Drupal);
