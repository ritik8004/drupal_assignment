/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.plpSwatchHover = {
    attach: function (context, settings) {

      /**
       * Adding the hover effect to colour swatches on plp.
       */
      $('.product-plp-detail-wrapper .swatches a').find('img').on('mouseover', debounce(function() {
        e.preventDefault();
        var ProductUrl = $(this).attr('data-sku-image');
        $(this).closest('.c-products__item').find('.alshaya_search_mainimage img').attr('src', ProductUrl);
      }, 500));

      $('.product-plp-detail-wrapper .swatches a').find('img').on('mouseout', function (e) {
        e.preventDefault();
      });
    }
  };

})(jQuery, Drupal);
