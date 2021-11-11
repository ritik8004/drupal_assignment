(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productModalView = {
    attach: function (context) {
      // Dispatch event on modal load each time to perform action on load.
      // Like we are rendering Sofa form on modal load event.
      var productModalViewEvent = new CustomEvent('onModalLoad', { bubbles: true });
      document.dispatchEvent(productModalViewEvent);

      // On modal load, check if sofa sectional form classes are present
      // and add initial price range block and render gallery without
      // any default SKU selection.
      var modalFormEle = $('.has-sofa-sectional-modal-form form.sku-base-form');
      if (modalFormEle.data('sku')) {
        var sku = modalFormEle.data('sku');
        var viewMode = modalFormEle.parents('article.entity--type-node').attr('data-vmode');
        var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
        var node = modalFormEle.parents('article.entity--type-node:first');
        // Check if the parent SKU is passed in event details and
      // add initial price block for the case of parent SKU.
        $('.price-block-' + drupalSettings[productKey][sku].identifier, node).html(drupalSettings[productKey][sku].price);

        // Update Gallery.
        Drupal.updateGallery(
          node,
          drupalSettings[productKey][sku].layout,
          drupalSettings[productKey][sku].gallery
        );
      }
    }
  };
})(jQuery, Drupal);
