/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.plpListingHeight = {
    attach: function (context, settings) {
      if ($(window).width() > 1024) {
        var Hgt = 0;
        $('.c-products__item').each(function () {
          var Height = $(this).find('> article').outerHeight(true);
          Hgt = (Hgt > Height) ? Hgt : Height;
        });

        $('.c-products__item').css('height', Hgt);
      }
    }
  };

})(jQuery, Drupal);
