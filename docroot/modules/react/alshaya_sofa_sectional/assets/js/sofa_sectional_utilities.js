(function ($, Drupal) {
  'use strict';

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
          // Check if the parent SKU is passed in event details and
          // add initial price block for the case of parent SKU.
          var viewMode = $('form.sku-base-form').parents('article.entity--type-node').attr('data-vmode');
          var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
          var node = $('form.sku-base-form').parents('article.entity--type-node:first');
          $('.price-block-' + drupalSettings[productKey][sku].identifier, node).html(drupalSettings[productKey][sku].price);

          // Update Gallery.
          Drupal.updateGallery(
            node,
            drupalSettings[productKey][sku].layout,
            drupalSettings[productKey][sku].gallery
          );
        }
      });
    }
  };
})(jQuery, Drupal);
