(function ($, Drupal) {

  Drupal.behaviors.sofaSectionalUtilities = {
    attach: function(context) {
      // This event listener Further trigger jquery event to refresh gallery, price block etc,
      // when variant is selected in react sofa sectional component.
      document.addEventListener('react-variant-select', function ({ detail }) {
        const { variant, sku } = detail || {};
        if (typeof variant !== 'undefined') {
          $('form.sku-base-form').trigger(
            'variant-selected',
            [
              variant,
              null
            ]
          );
        }
        else if (typeof sku !== 'undefined') {
          // Check if the target element selector is passed in event details,
          // else use the default sku base form class.
          var targetFormEle = typeof detail.elementSelector !== 'undefined'
            ? $(detail.elementSelector)
            : $('form.sku-base-form');

          // If the parent SKU is passed in event details and
          // add initial price block for the case of parent SKU.
          var viewMode = targetFormEle.parents('article.entity--type-node').attr('data-vmode');
          var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
          var node = targetFormEle.parents('article.entity--type-node:first');
          var pageMainSku = node.attr('data-sku');
          $('.price-block-' + drupalSettings[productKey][sku].identifier, node).html(drupalSettings[productKey][sku].price);

          // Update Gallery.
          window.commerceBackend.updateGallery(
            node,
            drupalSettings[productKey][sku].layout,
            drupalSettings[productKey][sku].gallery,
            pageMainSku,
            sku
          );
        }
      });
    }
  };
})(jQuery, Drupal);
