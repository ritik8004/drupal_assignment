/**
 * Listens to the 'rcsUpdateResults' event and update configurable attributes.
 */
 (function main() {
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined'
      || (e.detail.pageType !== 'product' && e.detail.placeholder !== 'product_by_sku')) {
      return;
    }

    var data = e.detail.result;
    // Update variant to include configurable attributes.
    if (data.type_id === 'configurable') {
      data.variants.forEach(function eachValue(variant) {
        variant.attributes.forEach(function eachAttr(attr) {
          variant.product[attr.code] = attr.value_index;
        });
      });
    }

  });

  // Alter variant configurable options.
  document.addEventListener('rcsProductConfigurablesAlter', function alterProduct(e) {
    var variant = e.detail.data.variant;
    variant.attributes.forEach(function eachAttr(attr) {
      variant.product[attr.code] = attr.value_index;
    });
  });

})();
