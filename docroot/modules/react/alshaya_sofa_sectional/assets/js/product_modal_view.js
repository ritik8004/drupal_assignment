(function ($, Drupal) {

  Drupal.behaviors.productModalView = {
    attach: function (context) {
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
        window.commerceBackend.updateGallery(
          node,
          drupalSettings[productKey][sku].layout,
          drupalSettings[productKey][sku].gallery,
          sku
        );
      }
    }
  };
})(jQuery, Drupal);
