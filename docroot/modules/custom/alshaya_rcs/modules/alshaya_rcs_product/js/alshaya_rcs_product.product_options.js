(function () {
  document.addEventListener('alshayaRcsUpdateResults', function getProductOptionsData(e) {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined'
      || (e.detail.pageType !== 'product' && e.detail.placeholder !== 'product-recommendation')) {
      return;
    }

    // The original object will also be modified in this process.
    const mainProduct = e.detail.result;

    if (mainProduct.type_id !== 'configurable') {
      return;
    }

    const attributeValues = [];
    const processedColors = [];
    mainProduct.variants.forEach(function (variant) {
      // Color attribute is set in alshaya_rcs_product.grouped_products.js.
      const colorAttribute = variant.product.color_attribute;
      if (!Drupal.hasValue(colorAttribute) || processedColors.includes(variant.product.color)) {
        return;
      }
      processedColors.push(variant.product.color);

      const allOptionsForAttribute = globalThis.rcsPhCommerceBackend.getDataAsync('product-option', { attributeCode: colorAttribute });
      attributeValues.push({value_index: variant.product.color, store_label: allOptionsForAttribute[variant.product.color]});
    });

    mainProduct.configurable_options.push({
      attribute_uid: btoa(drupalSettings.psudo_attribute),
      label: drupalSettings.alshayaRcs.colorLabel,
      position: -1,
      attribute_code: drupalSettings.alshayaRcs.colorAttribute,
      values: attributeValues,
    });

    // Sort the configurable options according to position.
    // @todo Ask if this should be done as done in
    // Configurable::getSortedConfigurableAttributes().
    mainProduct.configurable_options = mainProduct.configurable_options.sort(function (optionA, optionB) {
      return (optionA.position > optionB.position) - (optionA.position < optionB.position);
    });
  });
})();
