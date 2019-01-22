/**
 * @file
 * PLP Swatch Hover js file.
 */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.plpSwatchHover = {
    attach: function (context, settings) {

      /**
       * Adding the hover effect to colour swatches on plp.
       */
      if ($(window).width() >= 1024) {
        $('.product-plp-detail-wrapper .swatches').find('.swatch-image').once().on('mouseover', debounce(function (e) {
          e.preventDefault();
          var ProductUrl = $(this).find('img').attr('data-sku-image');
          $(this).closest('.c-products__item').find('.alshaya_search_mainimage img').attr('src', ProductUrl);
        }, 100));

        $('.product-plp-detail-wrapper .swatches').find('.swatch-image').on('mouseout', debounce(function (e) {
          e.preventDefault();

          var ProductUrl = $(this).closest('.c-products__item').find('.alshaya_search_mainimage').attr('data-sku-image');
          $(this).closest('.c-products__item').find('.alshaya_search_mainimage img').attr('src', ProductUrl);
        }, 100));
      }
    }
  };

})(jQuery, Drupal);
