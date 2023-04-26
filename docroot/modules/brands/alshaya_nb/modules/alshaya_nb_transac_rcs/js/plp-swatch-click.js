/**
 * @file
 * PLP Swatch Hover js file.
 */

/* global debounce */

(function ($, Drupal) {

  /**
   * Adding the hover effect to colour swatches on plp.
   */
   function onSwatchClickUpdateMainImage() {
    $('.product-plp-detail-wrapper .swatches').find('.swatch-image').on('click', debounce(function (e) {
      e.preventDefault();
      var productUrl = $(this).find('img').attr('data-sku-image');
      $(this).closest('.c-products__item').find('.alshaya_search_mainimage > img').attr('src', productUrl);
      var skuId = $(this).find('img').attr('data-sku-id');
      var url = $(this).closest('.c-products__item').find('a').attr('href');
      url = url.split('?');
      url = url[0] + '?selected=' + skuId;
      $(this).closest('.c-products__item').find('a.product-selected-url').attr('href', url);
    }, 100));
  }

  Drupal.behaviors.plpSwatchClick = {
    attach: function () {

      // This is for Algolia search that fires when Algolia search results return.
      $(document).once('searchResponseAddSwatchHover').on('search-results-updated plp-results-updated', function () {
        onSwatchClickUpdateMainImage();
      });

      // This is for Non-Algolia pages.
      onSwatchClickUpdateMainImage();
    }
  };
})(jQuery, Drupal);
