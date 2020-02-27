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
      function onSwatchHoverUpdateMainImage() {
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

      $(document).once('searchResponseAddSwatchHover').on('search-results-updated', function() {
        onSwatchHoverUpdateMainImage();
      });

      // Update ?selected param on hover and show specific variant on PDP
      // if show_variants_thumbnail_plp_gallery is set to true.
      if (settings.show_variants_thumbnail_plp_gallery) {
        $('.alshaya_search_slider ul li').once().on('mouseover', debounce(function (e) {
          e.preventDefault();
          var URL = $(this).closest('.c-products__item').find('a').attr('href');
          var SkuId = $(this).attr('data-sku-id');
          URL = URL.split('?');
          URL = URL[0] + '?selected=' + SkuId;
          $(this).closest('.c-products__item').find('a').attr('href', URL);
        }, 100));

        $('.alshaya_search_slider ul li').on('mouseout', debounce(function (e) {
          e.preventDefault();
          var URL = $(this).closest('.c-products__item').find('a').attr('href');
          var SkuId = $(this).attr('data-sku-id');
          URL = URL.split('?');
          URL = URL[0] + '?selected=' + SkuId;
          $(this).closest('.c-products__item').find('a').attr('href', URL);
        }, 100));
      }
    }
  };

})(jQuery, Drupal);
