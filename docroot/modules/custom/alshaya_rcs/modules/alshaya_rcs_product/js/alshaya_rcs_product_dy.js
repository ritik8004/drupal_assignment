/**
 * @file
 * Event Listener to alter dynamicYield.
 */

(function () {
  document.addEventListener('alterInitialDynamicYield', (e) => {
    // Alter the DY recommendationContext.
    if (e.detail.type === 'product') {
      e.detail.data.recommendationContext = e.detail.data.recommendationContext || {};
      e.detail.data.recommendationContext['type'] = 'PRODUCT';

      var product = e.detail.page_entity;

      // For configurable products, get the SKU of the first available variant.
      // @see ProductDyPageTypeEventSubscriber
      if (product.type_id === 'configurable' && product.variants && product.variants.length > 0) {
        // For configurable products, use the first available SKU.
        e.detail.data.recommendationContext['data'] = [
          product.variants[0].product.sku,
        ];
      } else {
        e.detail.data.recommendationContext['data'] = [product.sku];
      }
    }
  });
})();
