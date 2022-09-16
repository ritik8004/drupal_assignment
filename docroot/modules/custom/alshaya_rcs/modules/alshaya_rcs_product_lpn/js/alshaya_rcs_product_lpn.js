/**
 * Listens to the 'rcsUpdateResults' event and update configurable attributes.
 */
 (function main() {

   /**
   * Update the configurable options for the given product.
   *
   * @param {object} product
   *  The main product object.
   */
  function updateVariantAttributes(product) {
    product.variants.forEach(function eachValue(variant) {
      variant.attributes.forEach(function eachAttr(attr) {
        variant.product[attr.code] = attr.value_index;
      });
    });
  }

  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined'
      || (e.detail.pageType !== 'product'
      && e.detail.placeholder !== 'product_by_sku'
      && e.detail.placeholder !== 'products-in-style')) {
      return;
    }

    // Update variant to include configurable attributes.
    if (Array.isArray(e.detail.result)) {
      e.detail.result.forEach(function eachValue(product) {
        if (product.type_id === 'configurable') {
          updateVariantAttributes(product);
        }
      });
    }
    else {
      if (e.detail.result.type_id === 'configurable') {
        updateVariantAttributes(e.detail.result);
      }
    }
  });

})();
