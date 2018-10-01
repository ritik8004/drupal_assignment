(function ($) {
  'use strict';

  Drupal.behaviors.SlickSizeFilterSlider = {
    attach: function (context, settings) {
      var filterOptions = {
        slidesToShow: 2,
        vertical: false,
        arrows: true,
        focusOnSelect: false,
        infinite: false,
        touchThreshold: 1000,
      };

      function applyRtl(ocObject, options) {
        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.slick(
            $.extend({}, options, {rtl: true})
          );
          if (context !== document) {
            ocObject.slick('resize');
          }
        }
        else {
          ocObject.slick(options);
          if (context !== document) {
            ocObject.slick('resize');
          }
        }
      }

      if ($(window).width() > 1024) {
        applyRtl($('.sfb-band-cup .sfb-facets-container', context), filterOptions);
      }
    }
  };

}(jQuery));
