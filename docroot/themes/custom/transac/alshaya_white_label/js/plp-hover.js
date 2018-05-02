/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.plpListingHeight = {
    attach: function (context, settings) {
      var Hgt = 0;
      $('.c-products__item').each(function () {
        var Height = $(this).height();
        Hgt = (Hgt > Height) ? Hgt : Height;
      });

      $('.c-products__item').css('height', Hgt + 10);
      $('.c-products__item').hover(function () {
        var dwHeight = $(this).find('.product-plp-detail-wrapper').height();
        $(this).find('article').css('height', Hgt + dwHeight + 30);
      }, function () {
        $('.c-products__item').css('height', Hgt);
      });
    }
  };

})(jQuery, Drupal);


